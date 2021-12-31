<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use SimpleCli\Attribute\Argument;
use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\Rest;
use SimpleCli\Attribute\Validation;
use SimpleCli\Attribute\Values;
use SimpleCli\Command;
use SimpleCli\Exception\InvalidArgumentException;

// phpcs:disable Generic.Files.LineLength

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
        } catch (ReflectionException) {
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
                InvalidArgumentException::DUPLICATE_ATTRIBUTE,
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

        return match ($result) {
            'class-string' => 'string',
            default        => $result,
        };
    }

    private function cleanPhpDocComment(string $doc): string
    {
        $doc = (string) preg_replace('/^\s*\/\*+[\t ]*/', '', $doc);
        $doc = (string) preg_replace('/\s*\*+\/$/', '', $doc);
        $doc = (string) preg_replace('/^\s*\*\s?/m', '', $doc);

        return rtrim($doc);
    }

    /**
     * @param Option|string|null       $option
     * @param Argument|string|null     $argument
     * @param Rest|string|null         $rest
     * @param string                   $name
     * @param string|null              $doc
     * @param Values|array|string|null $values
     * @param string|null              $type
     * @param Validation[]             $validation
     *
     * @return void
     */
    private function addExpectation(
        Option|string|null $option,
        Argument|string|null $argument,
        Rest|string|null $rest,
        string $name,
        ?string $doc,
        Values|array|string|null $values,
        ?string $type,
        array $validation,
    ): void {
        if ($option !== null) {
            if ($argument !== null) {
                throw new InvalidArgumentException(
                    'A property cannot be both #Option / @option and #Argument / @argument',
                    InvalidArgumentException::ATTRIBUTE_CONFLICT,
                );
            }

            $optionInfo = $this->extractOptionInfo($option, $name, $doc, $values, $type);

            $optionInfo['names'] = array_filter($optionInfo['names']);
            $optionInfo['validation'] = $validation;

            if (!count($optionInfo['names'])) {
                $optionInfo['names'] = [$name, substr($name, 0, 1)];
            }

            $this->expectedOptions[] = $optionInfo;

            return;
        }

        if ($argument !== null || $rest !== null) {
            $definition = $this->extractArgumentInfo($argument, $rest, $name, $doc, $values, $type);
            $definition['validation'] = $validation;

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
    //          array<array-key, array{description: string, property: string, type: null|string, validation?: array<array-key, SimpleCli\Attribute\Validation>, values: array<array-key, mixed>|null}>
    //non-empty-array<array-key, array{description: string, property: string, type: null|string, validation?: array<array-key, SimpleCli\Attribute\Validation|mixed>, values: array<array-key, mixed>|null}>
    //

    /**
     * @param Option|string|null          $option
     * @param string                      $name
     * @param string|null                 $doc
     * @param Values|string[]|string|null $values
     * @param string|null                 $type
     *
     * @return array{type: ?string, property: string, values: ?array, description: string, names: array<string>}
     */
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
                'names'       => array_map(
                    'strval',
                    array_merge((array) ($option->name ?? []), (array) ($option->alias ?? [])),
                ),
                'description' => $option->description ?? '',
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

    /**
     * @param Argument|string|null        $argument
     * @param Rest|string|null            $rest
     * @param string                      $name
     * @param string|null                 $doc
     * @param Values|string[]|string|null $values
     * @param string|null                 $type
     *
     * @return array{type: ?string, property: string, values: ?array, description: string}
     */
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
            $var = $this->extractAnnotation($doc, 'var');
            $psalmVar = $this->extractAnnotation($doc, 'psalm-var');
            $type = $this->normalizeScalarType(
                $var
                    ?? $this->getPropertyType($property, $command, $rest)
                    ?? $psalmVar
            );

            $doc = trim($doc);

            if ($option === '') {
                $option = "$name, ".substr($name, 0, 1);
            }

            $this->addExpectation(
                $option,
                $argument,
                $rest,
                $name,
                $doc,
                $values,
                $type,
                array_map(
                    static fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
                    $property->getAttributes(Validation::class, ReflectionAttribute::IS_INSTANCEOF),
                ),
            );
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

    private function getTypeAndRestDescription(ReflectionProperty $property, Rest|string|null $rest): array
    {
        if ($rest instanceof Rest) {
            $rest = $rest->description;
        }

        return [$rest, $this->getTypeName($property->getType())];
    }

    private function getTypeName(?ReflectionType $type): ?string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(
                fn (ReflectionType $subType) => $this->getTypeName($subType),
                $type->getTypes(),
            ));
        }

        return $type instanceof ReflectionNamedType
            ? ($type->allowsNull() ? '?' : '').$type->getName()
            : null;
    }

    private function getPropertyType(ReflectionProperty $property, Command $command, Rest|string|null $rest): ?string
    {
        [$description, $type] = $this->getTypeAndRestDescription($property, $rest);

        if (!$type) {
            $defaultValue = $property->hasDefaultValue() ? $property->getValue($command) : null;

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
