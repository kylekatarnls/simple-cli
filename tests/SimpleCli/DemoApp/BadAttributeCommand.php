<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Argument;
use SimpleCli\Attribute\Option;
use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Invalid command.
 */
class BadAttributeCommand implements Command
{
    #[Argument('Hybrid not allowed.')]
    #[Option('Hybrid not allowed.')]
    public string $double = '';

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
