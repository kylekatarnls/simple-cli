<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Rest;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class ArrayRestAttributeOnlyCommand implements Command
{
    use Help;

    #[Rest('All arguments')]
    public array $all = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine(implode('|', $this->all));

        return true;
    }
}
