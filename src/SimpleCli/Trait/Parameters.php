<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use SimpleCli\Exception\InvalidArgumentException;
use Throwable;

trait Parameters
{
    /** @var string[] */
    protected array $parameters;

    /**
     * Get raw parameters (options and arguments) not filtered.
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Cast argument/option according to type in the definition.
     *
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     *
     * @param string                                                  $parameter
     * @param array{type: ?string, property: ?string, values: ?array} $parameterDefinition
     *
     * @return string|int|float|bool|null
     */
    public function getParameterValue(string $parameter, array $parameterDefinition): string|int|float|bool|null
    {
        $types = $this->getTypesFromDefinition($parameterDefinition);
        $value = $this->castToTypes($parameter, $types);

        if ($parameter !== '' &&
            $parameterDefinition['values'] &&
            // @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
            !in_array($parameter, $parameterDefinition['values'], true)
        ) {
            throw new InvalidArgumentException(
                'The parameter '.((string) $parameterDefinition['property']).
                ' must be one of the following values: ['.
                // @phan-suppress-next-line PhanParamSpecial1
                implode(', ', $parameterDefinition['values']).
                "]; '$parameter' given.",
                InvalidArgumentException::INVALID_VALUE,
            );
        }

        return $value;
    }

    protected function getTypesFromDefinition(array $parameterDefinition): array
    {
        $types = $parameterDefinition['type'] ?? 'string';
        $nullable = str_contains("|$types", '|?');
        $types = explode('|', strtr($types, ['?' => '']));

        if ($nullable) {
            $types[] = 'null';
        }

        return array_unique($types);
    }

    protected function castToTypes(mixed &$parameter, array $types): mixed
    {
        $value = $parameter;
        $exceptions = [];

        foreach ($types as $type) {
            try {
                settype($value, $type);

                break;
            } catch (Throwable $exception) {
                $exceptions[] = new InvalidArgumentException(
                    "Cannot cast $parameter to $type",
                    InvalidArgumentException::UNABLE_TO_CAST,
                    $exception,
                );
            }
        }

        if (count($exceptions) === count($types)) {
            throw $exceptions[0];
        }

        return $value;
    }
}
