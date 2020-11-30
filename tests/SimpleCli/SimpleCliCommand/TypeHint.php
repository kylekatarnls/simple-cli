<?php

declare(strict_types=1);

namespace Tests\SimpleCli\SimpleCliCommand;

use SimpleCli\Annotation\argument;
use SimpleCli\Annotation\option;
use SimpleCli\Annotation\rest;
use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Class TypeHint.
 *
 * @psalm-suppress MissingConstructor
 */
class TypeHint implements Command
{
    /** @argument */
    public bool $foo;

    /** @option */
    public float $bar = 8;

    /**
     * @rest
     *
     * @var float[]
     */
    public array $biz = [1];

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
