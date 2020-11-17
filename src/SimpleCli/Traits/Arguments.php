<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;

trait Arguments
{
    /** @var array<string, string|int|float|bool|null> */
    protected $arguments;

    /** @var array<array<string, mixed>> */
    protected $expectedArguments;

    /** @var array<string|int|float|bool|null> */
    protected $restArguments;

    /** @var array<string, mixed>|null */
    protected $expectedRestArgument;

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
     * @return array<string, mixed>|null
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
                $this->restArguments[] = $this->getParameterValue($argument, $restDefinition);

                return;
            }

            $count = count($this->expectedArguments);

            throw new InvalidArgumentException(
                'Expect only '.$count.' argument'.($count === 1 ? '' : 's')
            );
        }

        // @phan-suppress-next-line PhanUndeclaredMethod
        $this->arguments[$definition['property']] = $this->getParameterValue($argument, $definition);
    }
}
