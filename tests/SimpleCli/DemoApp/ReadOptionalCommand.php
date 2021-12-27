<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\GetFileContent;
use SimpleCli\Attribute\Option;
use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;

/**
 * Read an input file.
 */
class ReadOptionalCommand extends CommandBase
{
    #[Option]
    #[GetFileContent]
    public ?string $input;

    public function run(SimpleCli $cli): bool
    {
        $cli->write($this->input ?? 'empty');

        return true;
    }
}
