<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
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
     * @param string $parameter
     * @param array<string, (string|null)> $parameterDefinition
     *
     * @return string|int|float|bool|null
     */
    public function getParameterValue(string $parameter, array $parameterDefinition): string|int|float|bool|null
    {
        $value = $parameter;

        try {
            settype($value, $parameterDefinition['type'] ?? 'string');
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                "Cannot cast $parameter to ".((string) $parameterDefinition['type']),
                0,
                $exception,
            );
        }

        if ($parameter !== '' &&
            $parameterDefinition['values'] &&
            // @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
            !in_array($parameter, array_map('trim', explode(',', $parameterDefinition['values'])))
        ) {
            throw new InvalidArgumentException(
                'The parameter '.((string) $parameterDefinition['property']).
                ' must be one of the following values: ['.$parameterDefinition['values']."]; '$parameter' given."
            );
        }

        return $value;
    }
}
