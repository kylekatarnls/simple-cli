<?php

declare(strict_types=1);

namespace Tests\SimpleCli\SimpleCliCommand;

use SimpleCli\Annotation\argument;
use SimpleCli\Annotation\option;
use SimpleCli\Annotation\rest;
use SimpleCli\Command;
use SimpleCli\SimpleCli;

class DefaultValue implements Command
{
    /**
     * @argument
     *
     * @psalm-var bool
     */
    public $foo = false;

    /**
     * @option
     *
     * @psalm-var float
     */
    public $bar = 8.0;

    /**
     * @rest
     *
     * @psalm-var (float|string)[]
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
