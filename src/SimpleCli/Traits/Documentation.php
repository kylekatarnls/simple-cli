<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use SimpleCli\Attribute\Argument;
use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\Rest;
use SimpleCli\Attribute\Values;
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

        return $this->cleanPhpDocComment($doc);
    }

    /**
     * Get an attribute if present or extract an annotation content from a PHP comment doc block.
     *
     * @phan-suppress PhanTypeMismatchDeclaredReturn
     *
     * @template T extends object
     *
     * @param string             $source
     * @param string             $annotation
     * @param ReflectionProperty $property
     * @param class-string<T>    $attributeClass
     *
     * @return string|T|null
     */
    public function getAttributeOrAnnotation(
        string &$source,
        string $annotation,
        ReflectionProperty $property,
        string $attributeClass,
    ): object|string|null {
        $attributes = $property->getAttributes($attributeClass);
        $count = count($attributes);

        if ($count > 1) {
            throw new InvalidArgumentException(
                "Only 1 attribute of $attributeClass can be set on a given property.",
            );
        }

        if ($count) {
            return $attributes[0]->newInstance();
        }

        return $this->extractAnnotation($source, $annotation);
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
                /** @var string $result */
                $result = str_replace("\n".str_repeat(' ', $length), "\n", $match[2] ?? '');

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

    private function addExpectation(
        Option|string|null $option,
        Argument|string|null $argument,
        Rest|string|null $rest,
        string $name,
        ?string $doc,
        Values|array|string|null $values,
        ?string $type,
    ): void {
        if ($option !== null) {
            if ($argument !== null) {
                throw new InvalidArgumentException(
                    'A property cannot be both #Option / @option and #Argument / @argument',
                );
            }

            $optionInfo = $this->extractOptionInfo($option, $name, $doc, $values, $type);

            $optionInfo['names'] = array_filter($optionInfo['names']);

            if (!count($optionInfo['names'])) {
                $optionInfo['names'] = [$name, substr($name, 0, 1)];
            }

            $this->expectedOptions[] = $optionInfo;

            return;
        }

        if ($argument !== null || $rest !== null) {
            $definition = $this->extractArgumentInfo($argument, $rest, $name, $doc, $values, $type);

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

    private function extractOptionInfo(
        Option|string|null $option,
        string $name,
        ?string $doc,
        Values|array|string|null $values,
        ?string $type,
    ): array {
        if ($option instanceof Option) {
            return [
                'property'    => $name,
                'names'       => array_merge((array) ($option->name ?? []), (array) ($option->alias ?? [])),
                'description' => $option->description,
                'values'      => $this->getValues($values),
                'type'        => $type,
            ];
        }

        $optionLine = preg_split('#\s*/\s*#', $option ?? '', 2) ?: [];
        $preDoc = trim($optionLine[1] ?? '');

        return [
            'property'    => $name,
            'names'       => array_map('trim', explode(',', $optionLine[0])),
            'description' => $this->concatDescription($preDoc, $doc),
            'values'      => $this->getValues($values),
            'type'        => $type,
        ];
    }

    private function extractArgumentInfo(
        Argument|string|null $argument,
        Rest|string|null $rest,
        string $name,
        ?string $doc,
        Values|array|string|null $values,
        ?string $type,
    ): array {
        if ($argument instanceof Argument) {
            $argument = $argument->description;
        }

        if ($rest instanceof Rest) {
            $rest = $rest->description;
        }

        /** @psalm-suppress PossiblyNullArgument */
        $preDoc = ltrim(trim($rest ?? $argument ?? ''), "/ \t");
        // @phan-suppress-previous-line PhanTypeMismatchArgumentNullableInternal

        return [
            'property'    => $name,
            'description' => $this->concatDescription($preDoc, $doc),
            'values'      => $this->getValues($values),
            'type'        => $type,
        ];
    }

    private function getValues(Values|array|string|null $values): ?array
    {
        if ($values instanceof Values) {
            return $values->values;
        }

        if (is_string($values)) {
            return array_map('trim', explode(',', $values));
        }

        return $values;
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
            $argument = $this->getAttributeOrAnnotation($doc, 'argument', $property, Argument::class);
            $rest = $this->getAttributeOrAnnotation($doc, 'rest', $property, Rest::class);
            $option = $this->getAttributeOrAnnotation($doc, 'option', $property, Option::class);
            $values = $this->getAttributeOrAnnotation($doc, 'values', $property, Values::class);
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

    private function getRestTypeAndDescription(ReflectionProperty $property, Rest|string|null $rest): array
    {
        if ($rest instanceof Rest) {
            $rest = $rest->description;
        }

        $type = $property->getType();
        $type = $type instanceof ReflectionNamedType
            ? $type->getName()
            : null;

        return [$rest, $type];
    }

    private function getPropertyType(ReflectionProperty $property, Command $command, Rest|string|null $rest): ?string
    {
        [$description, $type] = $this->getRestTypeAndDescription($property, $rest);

        if (!$type) {
            $defaultValue = $property->getValue($command);

            if ($defaultValue !== null) {
                $type = gettype($defaultValue);
            }
        }

        if ($description !== null && ($type ?? 'array') === 'array') {
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
}
