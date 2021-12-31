<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use SimpleCli\Attribute\Validation;
use SimpleCli\Command as SimpleCliCommand;
use SimpleCli\Exception\InvalidArgumentException;
use SimpleCli\Writer;
use Stringable;

trait Validations
{
    /**
     * @param string|Stringable|int|float $name
     * @param mixed                       $value
     * @param Validation[]                $validation
     *
     * @return mixed
     */
    protected function validateValueWith(string|Stringable|int|float $name, mixed $value, array $validation): mixed
    {
        foreach ($validation as $validator) {
            $error = $validator->proceed($value);

            if ($error) {
                throw new InvalidArgumentException(
                    'Validation failed for '.
                    ((string) $name).': '.
                    $error,
                    InvalidArgumentException::FAILED_VALIDATION,
                );
            }
        }

        return $value;
    }

    protected function validateExpectedOptions(SimpleCliCommand $commander): ?SimpleCliCommand
    {
        try {
            // @phan-suppress-next-line PhanUndeclaredMethod
            foreach ($this->getExpectedOptions() as $definition) {
                $validation = $definition['validation'] ?? [];

                if ($validation) {
                    $property = $definition['property'];
                    $value = $this->validateValueWith(
                        $definition['names'][0] ?? $property,
                        $commander->$property ?? null,
                        $validation,
                    );

                    // @phan-suppress-next-line PhanUndeclaredMethod
                    if ($value === null && !in_array('null', $this->getTypesFromDefinition($definition), true)) {
                        throw new InvalidArgumentException(
                            "$property is mandatory.",
                            InvalidArgumentException::MANDATORY_PROPERTY,
                        );
                    }

                    $commander->$property = $value;
                }
            }
        } catch (\InvalidArgumentException $exception) {
            if ($this instanceof Writer) {
                $this->write($exception->getMessage(), 'red');
            }

            return null;
        }

        return $commander;
    }
}
