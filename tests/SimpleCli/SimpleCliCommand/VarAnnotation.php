<?php

declare(strict_types=1);

namespace Tests\SimpleCli\SimpleCliCommand;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Class VarAnnotation.
 *
 * @psalm-suppress MissingConstructor
 */
class VarAnnotation implements Command
{
    /**
     * @argument
     *
     * @var bool
     */
    public $foo;

    /**
     * @option
     *
     * @var float
     */
    public $bar = 8;

    /**
     * @rest
     *
     * @var float[]
     */
    public $biz = [];

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
