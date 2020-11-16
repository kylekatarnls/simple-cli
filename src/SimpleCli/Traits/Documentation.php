<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use ReflectionType;
use SimpleCli\Command;

/**
 * Trait Documentation.
 *
 * @property array[]|null $expectedOptions
 * @property array[]|null $expectedArguments
 * @property array|null   $expectedRestArgument
 */
trait Documentation
{
    /**
     * Get PHP comment doc block content of a given class.
     *
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return string
     */
    public function extractClassNameDescription(string $className): string
    {
        try {
            /** @psalm-var class-string $className */
            $doc = (new ReflectionClass($className))->getDocComment();
        } catch (\ReflectionException $e) {
            $doc = null;
        }

        if (empty($doc)) {
            return $className;
        }

        return $this->cleanPhpDocComment((string) $doc);
    }

    /**
     * Extract an annotation content from a PHP comment doc block.
     *
     * @param string $source
     * @param string $annotation
     *
     * @return string|null
     */
    public function extractAnnotation(string &$source, string $annotation): ?string
    {
        $code = "@$annotation";
        $length = strlen($code) + 1;
        $result = null;

        $source = (string) preg_replace_callback(
            '/^'.preg_quote($code).'( ([^\n]*(\n+'.str_repeat(' ', $length).'[^\n]*)*))?/m',
            function (array $match) use (&$result, $length) {
                $result = (string) str_replace("\n".str_repeat(' ', $length), "\n", $match[2] ?? '');

                return '';
            },
            $source
        );

        $source = trim($source, "\n");

        return $result;
    }

    private function cleanPhpDocComment(string $doc): string
    {
        $doc = (string) preg_replace('/^\s*\/\*+[\t ]*/', '', $doc);
        $doc = (string) preg_replace('/\s*\*+\/$/', '', $doc);
        $doc = (string) preg_replace('/^\s*\*\s?/m', '', $doc);

        return rtrim($doc);
    }

    /**
     * @param ?string $option
     * @param ?string $argument
     * @param ?string $rest
     * @param ?string $name
     * @param ?string $doc
     * @param ?string $values
     * @param ?string $type
     */
    private function addExpectation($option, $argument, $rest, $name, $doc, $values, $type): void
    {
        if ($option !== null) {
            if ($argument !== null) {
                throw new InvalidArgumentException(
                    'A property cannot be both @option and @argument'
                );
            }

            $optionLine = preg_split('#\s*/\s*#', $option, 2) ?: [];
            $preDoc = trim($optionLine[1] ?? '');

            $this->expectedOptions[] = [
                'property'    => $name,
                'names'       => array_map('trim', explode(',', $optionLine[0])),
                'description' => $this->concatDescription($preDoc, $doc),
                'values'      => $values,
                'type'        => $type,
            ];

            return;
        }

        if ($argument !== null || $rest !== null) {
            $preDoc = ltrim(trim($rest ?? $argument ?? ''), "/ \t");

            $definition = [
                'property'    => $name,
                'description' => $this->concatDescription($preDoc, $doc),
                'values'      => $values,
                'type'        => $type,
            ];

            if ($rest !== null) {
                $definition['type'] = $definition['type'] === 'array'
                    ? 'string'
                    : preg_replace('/\[]$/', '', $definition['type'] ?: '');
                $this->expectedRestArgument = $definition;

                return;
            }

            $this->expectedArguments[] = $definition;
        }
    }

    private function concatDescription(string $start, ?string $end): string
    {
        $end = $end ?? '';

        return $start.($start !== '' && $end !== '' ? "\n" : '').$end;
    }

    private function extractExpectations(Command $command): void
    {
        $this->expectedRestArgument = null;
        $this->expectedArguments = [];
        $this->expectedOptions = [];

        foreach ((new ReflectionObject($command))->getProperties() as $property) {
            $name = $property->getName();
            $doc = $this->cleanPhpDocComment((string) $property->getDocComment());
            $argument = $this->extractAnnotation($doc, 'argument');
            $rest = $this->extractAnnotation($doc, 'rest');
            $option = $this->extractAnnotation($doc, 'option');
            $values = $this->extractAnnotation($doc, 'values');
            $type = $this->normalizeScalarType(
                $this->extractAnnotation($doc, 'var')
                    ?? $this->getPropertyType($property, $command, $rest)
            );

            $doc = trim($doc);

            if ($option === '') {
                $option = "$name, ".substr($name, 0, 1);
            }

            $this->addExpectation($option, $argument, $rest, $name, $doc, $values, $type);
        }
    }

    private function normalizeScalarType(?string $type): string
    {
        return strtr($type ?: 'string', [
            'boolean' => 'bool',
            'integer' => 'int',
            'double'  => 'float',
            'decimal' => 'float',
        ]);
    }

    private function getPropertyType(ReflectionProperty $property, Command $command, ?string $rest): ?string
    {
        $type = $this->getPropertyTypeByHint($property);
        $type = $type instanceof ReflectionNamedType
            ? $type->getName()
            : null;

        if (!$type) {
            $defaultValue = $property->getValue($command);

            if ($defaultValue !== null) {
                $type = gettype($defaultValue);
            }
        }

        if ($rest !== null && ($type ?? 'array') === 'array') {
            $defaultValue = $defaultValue ?? $property->getValue($command);

            if (is_iterable($defaultValue)) {
                $types = [];

                foreach ($defaultValue as $value) {
                    $types[$this->normalizeScalarType(gettype($value))] = true;
                }

                if (count($types) > 0) {
                    return implode('|', array_keys($types));
                }
            }
        }

        return $type;
    }

    /**
     * Return the typehint of a property if PHP >= 7.4 is running and a type hint is available,
     * else return null silently.
     *
     * @param ReflectionProperty $property
     *
     * @return ReflectionType|null
     */
    private function getPropertyTypeByHint(ReflectionProperty $property)
    {
        /** @var mixed $property */
        // @phan-suppress-next-line PhanUndeclaredMethod
        return method_exists($property, 'getType') ? $property->getType() : null;
    }
}
