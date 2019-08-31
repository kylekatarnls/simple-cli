<?php

namespace Tests\SimpleCli\DemoApp;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class InteractiveCli extends DemoCli
{
    protected $answers = [];

    /**
     * @param array $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    public function read($prompt, $completion = null): string
    {
        return array_shift($this->answers) ?: '';
    }
}
