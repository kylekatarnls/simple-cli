<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

class DefaultValue implements Command
{
    /**
     * @argument
     */
    public $foo = false;

    /**
     * @option
     */
    public $bar = 8.0;

    /**
     * @rest
     */
    public $biz = [2.0, 'ok'];

    /**
     * @param SimpleCli $cli
     *
     * @return bool
     */
    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine($this->foo ? 'yes' : 'no');
        $cli->writeLine((string) ($this->bar * 2));

        return true;
    }
}
