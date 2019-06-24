<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class ArrayRestCommand implements Command
{
    /**
     * @option bar, biz
     *
     * @var integer
     */
    public $foo = 9;

    /**
     * @rest
     *
     * All arguments
     *
     * @var array
     */
    public $all = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine(var_export($this->foo, true));
        $cli->writeLine(implode('|', $this->all));

        return true;
    }
}
