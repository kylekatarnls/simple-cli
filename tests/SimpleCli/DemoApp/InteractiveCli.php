<?php

namespace Tests\SimpleCli\DemoApp;

use Closure;
use SimpleCli\Command;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class InteractiveCli extends DemoCli
{
    /**
     * @var string[]
     */
    protected $answers = [];

    /**
     * @param string[] $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * Ask the user $prompt and return the CLI input.
     *
     * @param string                         $prompt
     * @param Closure|callable|string[]|null $completion
     *
     * @return string
     */
    public function read($prompt, $completion = null): string
    {
        return array_shift($this->answers) ?: '';
    }

    /**
     * Return an array of traits directly in use by the given command class.
     *
     * @param Command|string $command
     *
     * @return string[]
     */
    public function traits($command)
    {
        return $this->getCommandTraits($command);
    }
}
