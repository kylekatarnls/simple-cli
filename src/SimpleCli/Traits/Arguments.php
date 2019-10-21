<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use InvalidArgumentException;

trait Arguments
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $expectedArguments;

    /**
     * @var array
     */
    protected $restArguments;

    /**
     * @var array|null
     */
    protected $expectedRestArgument;

    /**
     * Get list of current filtered arguments.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get definitions of expected arguments.
     *
     * @return array[]
     */
    public function getExpectedArguments(): array
    {
        return $this->expectedArguments;
    }

    /**
     * Get the rest of filtered arguments.
     *
     * @return array
     */
    public function getRestArguments(): array
    {
        return $this->restArguments;
    }

    /**
     * Get definition for the rest argument if a @rest property given.
     *
     * @return array|null
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
