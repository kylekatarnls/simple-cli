<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;
use SimpleCli\Trait\Input;

/**
 * This is a demo.
 */
class TraitCommand implements Command
{
    use Verbose;
    use Input;

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
