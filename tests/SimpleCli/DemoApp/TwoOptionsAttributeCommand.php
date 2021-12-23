<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Option;
use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Invalid command.
 */
class TwoOptionsAttributeCommand implements Command
{
    #[Option('First option')]
    #[Option('Second option')]
    public string $double = '';

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
