<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\GetFileContent;
use SimpleCli\Attribute\Option;
use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;

/**
 * Show union type.
 */
class UnionTypeCommand extends CommandBase
{
    #[Option]
    public string|float|int $input;

    public function run(SimpleCli $cli): bool
    {
        $cli->write(gettype($this->input));

        return true;
    }
}
