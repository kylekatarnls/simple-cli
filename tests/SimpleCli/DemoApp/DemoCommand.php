<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class DemoCommand implements Command
{
    /**
     * @option
     *
     * If this option is set, extra debug information will be displayed.
     *
     * @var bool
     */
    public $verbose = false;

    /**
     * @option
     * @values hello, hi, bye
     *
     * Append a prefix to $sentence.
     *
     * @var string
     */
    public $prefix = '';

    /**
     * @argument
     *
     * Sentence to display.
     *
     * @var string
     */
    public $sentence = '';

    public function run(SimpleCli $cli): bool
    {
        $prefix = (string) $this->prefix;

        if ($this->verbose) {
            $cli->writeLine('prefix: '.$prefix);
        }

        $cli->writeLine($prefix.$this->sentence);

        return true;
    }
}
