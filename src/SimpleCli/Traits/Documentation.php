<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionObject;
use SimpleCli\Command;

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

        $source = (string) preg_replace_callback('/^'.preg_quote($code).'( ([^\n]*(\n+'.str_repeat(' ', $length).'[^\n]*)*))?/m', function ($match) use (&$result, $length) {
            $result = (string) str_replace("\n".str_repeat(' ', $length), "\n", $match[2] ?? '');

            return '';
        }, $source);

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

    private function extractExpectations(Command $command): void
    {
        $this->expectedArguments = [];
        $this->expectedOptions = [];

        foreach ((new ReflectionObject($command))->getProperties() as $property) {
            $name = $property->getName();
            $doc = $this->cleanPhpDocComment((string) $property->getDocComment());
            $argument = $this->extractAnnotation($doc, 'argument') !== null;
            $option = $this->extractAnnotation($doc, 'option');
            $values = $this->extractAnnotation($doc, 'values');
            $var = str_replace('boolean', 'bool', $this->extractAnnotation($doc, 'var') ?: 'string');
            $doc = trim($doc);

            if ($option === '') {
                $option = "$name, ".substr($name, 0, 1);
            }

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

                continue;
            }

            if ($argument) {
                $this->expectedArguments[] = [
                    'property'    => $name,
                    'description' => $doc,
                    'values'      => $values,
                    'type'        => $var,
                ];
            }
        }
    }
}
