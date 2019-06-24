<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Invalid command.
 */
class BadCommand implements Command
{
    /**
     * @argument
     * @option
     *
     * Hybrid not allowed.
     *
     * @var string
     */
    public $double = '';

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
