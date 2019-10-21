<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionObject;
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
        $doc = (string) preg_replace('/^\s*\/\*+/', '', $doc);
        $doc = (string) preg_replace('/\s*\*+\/$/', '', $doc);
        $doc = (string) preg_replace('/^\s*\*\s?/m', '', $doc);

        return rtrim($doc);
    }

    /**
     * @param ?string $option
     * @param bool    $argument
     * @param bool    $rest
     * @param ?string $name
     * @param ?string $doc
     * @param ?string $values
     * @param ?string $var
     */
    private function addExpectation($option, $argument, $rest, $name, $doc, $values, $var): void
    {
        if ($option) {
            if ($argument) {
                throw new InvalidArgumentException(
                    'A property cannot be both @option and @argument'
                );
            }

            $this->expectedOptions[] = [
                'property'    => $name,
                'names'       => array_map('trim', explode(',', $option)),
                'description' => $doc,
                'values'      => $values,
                'type'        => $var,
            ];

            return;
        }

        if ($argument || $rest) {
            $definition = [
                'property'    => $name,
                'description' => $doc,
                'values'      => $values,
                'type'        => $var,
            ];

            if ($rest) {
                $definition['type'] = $definition['type'] === 'array'
                    ? 'string'
                    : preg_replace('/\[]$/', '', $definition['type'] ?: '');
                $this->expectedRestArgument = $definition;

                return;
            }

            $this->expectedArguments[] = $definition;
        }
    }

    private function extractExpectations(Command $command): void
    {
        $this->expectedRestArgument = null;
        $this->expectedArguments = [];
        $this->expectedOptions = [];

        foreach ((new ReflectionObject($command))->getProperties() as $property) {
            $name = $property->getName();
            $doc = $this->cleanPhpDocComment((string) $property->getDocComment());
            $argument = $this->extractAnnotation($doc, 'argument') !== null;
            $rest = $this->extractAnnotation($doc, 'rest') !== null;
            $option = $this->extractAnnotation($doc, 'option');
            $values = $this->extractAnnotation($doc, 'values');
            $var = str_replace('boolean', 'bool', $this->extractAnnotation($doc, 'var') ?: 'string');
            $doc = trim($doc);

            if ($option === '') {
                $option = "$name, ".substr($name, 0, 1);
            }

            $this->addExpectation($option, $argument, $rest, $name, $doc, $values, $var);
        }
    }
}
