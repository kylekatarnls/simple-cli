<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use InvalidArgumentException;
use SimpleCli\Attribute\Validation;

// phpcs:disable Generic.Files.LineLength

trait Options
{
    /** @var array<string, mixed> */
    protected array $options;

    /** @var array<array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null}> */
    protected array $expectedOptions;

    /**
     * Get list of current filtered options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get definition of expected options.
     *
     * @return array<array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null, validation?: Validation[]}>
     */
    public function getExpectedOptions(): array
    {
        return $this->expectedOptions;
    }

    /**
     * Get option definition and expected types/values of a given one identified by name or alias.
     *
     * @param string $name
     *
     * @return array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null, validation?: Validation[]}
     */
    public function getOptionDefinition(string $name): array
    {
        foreach ($this->expectedOptions as $definition) {
            if (in_array($name, $definition['names'] ?? [], true)) {
                return $definition;
            }
        }

        $name = strlen($name) === 1 ? "-$name" : "--$name";

        throw new InvalidArgumentException(
            "Unknown $name option"
        );
    }

    /**
     * @param array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null} $definition
     * @param string                                                                                                 $name
     * @param string|null                                                                                            $value
     */
    private function enableBooleanOption(array $definition, string $name, string $value = null): void
    {
        if ($definition['type'] !== 'bool') {
            throw new InvalidArgumentException(
                "$name option is not a boolean, so you can't use it in a aliases group"
            );
        }

        if ($value) {
            throw new InvalidArgumentException(
                "$name option is boolean and should not have value"
            );
        }

        $this->options[$definition['property']] = true;
    }

    /**
     * @param string                                                                                                      $name
     * @param string|null                                                                                                 $value
     * @param array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null}|null $optionDefinition
     *
     * @param-out array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null} $optionDefinition
     */
    private function setOption(string $name, string $value = null, ?array &$optionDefinition = null): void
    {
        $definition = $this->getOptionDefinition($name);
        $name = strlen($name) === 1 ? "-$name" : "--$name";

        if ($definition['type'] === 'bool') {
            $this->enableBooleanOption($definition, $name, $value);

            return;
        }

        if ($value) {
            // @phan-suppress-next-line PhanUndeclaredMethod
            $this->options[$definition['property']] = $this->getParameterValue($value, $definition);

            return;
        }

        $optionDefinition = $definition;
    }

    /**
     * @param string                                                                                                      $option
     * @param array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null}|null $optionDefinition
     *
     * @param-out array{type: ?string, property: string, values: ?array, description: string, names: array<string>|null} $optionDefinition
     */
    private function parseOption(string $option, array &$optionDefinition = null): void
    {
        $parts = explode('=', $option, 2);
        $name = $parts[0];
        $value = $parts[1] ?? null;

        if (substr($name, 1, 1) !== '-') {
            if (strlen($name) > 2) {
                if ($value) {
                    throw new InvalidArgumentException(
                        "Unable to parse $option, maybe you would mean -$option"
                    );
                }

                foreach (str_split(substr($name, 1)) as $alias) {
                    $this->enableBooleanOption($this->getOptionDefinition($alias), "-$alias");
                }

                return;
            }

            $this->setOption(substr($name, 1), $value, $optionDefinition);

            return;
        }

        $this->setOption(substr($name, 2), $value, $optionDefinition);
    }
}
