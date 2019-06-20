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
     * @param array  $optionDefinition
     *
     * @return string
     */
    public function getParameterValue(string $parameter, array $optionDefinition)
    {
        if (!settype($parameter, $optionDefinition['type'] ?? 'string')) {
            throw new InvalidArgumentException(
                "Cannot cast $parameter to ".$optionDefinition['type']
            );
        }

        return $parameter;
    }
}
