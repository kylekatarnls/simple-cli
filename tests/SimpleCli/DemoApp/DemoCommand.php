<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;
use SimpleCli\Options\Help;
use SimpleCli\Options\Verbose;

/**
 * This is a demo.
 */
class DemoCommand implements Command
{
    use Verbose, Help;

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
