<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class RestCommand implements Command
{
    /**
     * @argument
     *
     * Sentence to display.
     *
     * @var string
     */
    public $sentence = '';

    /**
     * @rest
     *
     * Suffixes after the sentence.
     *
     * @var string[]
     */
    public $suffixes = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine($this->sentence.implode('', $this->suffixes));

        return true;
    }
}
