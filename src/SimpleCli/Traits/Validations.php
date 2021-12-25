<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;
use SimpleCli\Attribute\Validation;
use SimpleCli\Command as SimpleCliCommand;
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
                );
            }
        }

        return $value;
    }

    protected function validateExpectedOptions(SimpleCliCommand $commander): ?SimpleCliCommand
    {
        try {
            // @phan-suppress-next-line PhanUndeclaredProperty
            foreach (($this->expectedOptions ?? []) as $definition) {
                $validation = $definition['validation'] ?? [];

                if ($validation) {
                    $property = $definition['property'];
                    $commander->$property = $this->validateValueWith(
                        $definition['names'][0] ?? $property,
                        $commander->$property,
                        $validation,
                    );
                }
            }
        } catch (InvalidArgumentException $exception) {
            if ($this instanceof Writer) {
                $this->write($exception->getMessage(), 'red');
            }

            return null;
        }

        return $commander;
    }
}
