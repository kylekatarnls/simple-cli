<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Annotation\argument;
use SimpleCli\Annotation\rest;
use SimpleCli\Command;
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
     * @var array
     */
    public $suffixes = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine($this->sentence.implode('', $this->suffixes));

        return true;
    }
}
