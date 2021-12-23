<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\Rest;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class ArrayRestAttributeCommand implements Command
{
    use Help;

    #[Option('bar, biz, X, Y')]
    public int $foo = 9;

    /** @var string[] */
    #[Rest('All arguments')]
    public array $all = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine(var_export($this->foo, true));
        $cli->writeLine(implode('|', $this->all));

        return true;
    }
}
