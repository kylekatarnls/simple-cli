<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;

trait Parameters
{
    /**
     * @var string[]
     */
    protected $parameters;

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
     * @param string $parameter
     * @param array  $parameterDefinition
     *
     * @return string|int|float|bool|null
     */
    public function getParameterValue(string $parameter, array $parameterDefinition)
    {
        $value = $parameter;

        if (!@settype($value, $parameterDefinition['type'] ?? 'string')) {
            throw new InvalidArgumentException(
                "Cannot cast $parameter to ".$parameterDefinition['type']
            );
        }

        if ($parameter !== '' &&
            $parameterDefinition['values'] &&
            !in_array($parameter, array_map('trim', explode(',', $parameterDefinition['values'])))
        ) {
            throw new InvalidArgumentException(
                'The parameter '.$parameterDefinition['property'].
                ' must be one of the following values: ['.$parameterDefinition['values']."]; '$parameter' given."
            );
        }

        return $value;
    }
}
