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

    private function parseArgument(string $argument): void
    {
        $definition = $this->expectedArguments[count($this->arguments)] ?? null;

        if (!$definition) {
            $count = count($this->expectedArguments);

            throw new InvalidArgumentException(
                'Expect only '.$count.' argument'.($count === 1 ? '' : 's')
            );
        }

        $this->arguments[$definition['property']] = $this->getParameterValue($argument, $definition);
    }
}
