<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use InvalidArgumentException;
use SimpleCli\Attribute\Validation;

trait Arguments
{
    /** @var array<string, string|int|float|bool|null> */
    protected array $arguments;

    /** @var array<array{type: ?string, property: string, values: ?array, description: string, validation?: Validation[]}> */
    protected array $expectedArguments;

    /** @var array<string|int|float|bool|null> */
    protected array $restArguments;

    /** @var array{type: ?string, property: string, values: ?array, description: string, validation?: Validation[]}|null */
    protected ?array $expectedRestArgument;

    /**
     * Get list of current filtered arguments.
     *
     * @return array<string, string|int|float|bool|null>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get definitions of expected arguments.
     *
     * @return array<array<string, mixed>>
     */
    public function getExpectedArguments(): array
    {
        return $this->expectedArguments;
    }

    /**
     * Get the rest of filtered arguments.
     *
     * @return array<string|int|float|bool|null>
     */
    public function getRestArguments(): array
    {
        return $this->restArguments;
    }

    /**
     * Get definition for the rest argument if a @rest property given.
     *
     * @return array{type: ?string, property: string, values: ?array, description: string}|null
     */
    public function getExpectedRestArgument(): ?array
    {
        return $this->expectedRestArgument;
    }

    private function parseArgument(string $argument): void
    {
        $definition = $this->expectedArguments[count($this->arguments)] ?? null;

        if (!$definition) {
            $restDefinition = $this->getExpectedRestArgument();

            if ($restDefinition) {
                // @phan-suppress-next-line PhanUndeclaredMethod
                $this->restArguments[] = $this->validateValueWith(
                    $restDefinition['property'],
                    // @phan-suppress-next-line PhanUndeclaredMethod
                    $this->getParameterValue($argument, $restDefinition),
                    $restDefinition['validation'] ?? [],
                );

                return;
            }

            $count = count($this->expectedArguments);

            throw new InvalidArgumentException(
                'Expect only '.$count.' argument'.($count === 1 ? '' : 's')
            );
        }

        // @phan-suppress-next-line PhanUndeclaredMethod
        $this->arguments[$definition['property']] = $this->validateValueWith(
            $definition['property'],
            // @phan-suppress-next-line PhanUndeclaredMethod
            $this->getParameterValue($argument, $definition),
            $definition['validation'] ?? [],
        );
    }
}
