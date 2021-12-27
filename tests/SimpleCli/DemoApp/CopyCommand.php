<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\ReadableFile;
use SimpleCli\Attribute\WritableFile;
use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;

/**
 * Copy an input file into an output path.
 */
class CopyCommand extends CommandBase
{
    #[Option]
    #[ReadableFile]
    public string $inputFile;

    #[Option]
    #[WritableFile]
    public string $outputFile;

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine('copy '.$this->inputFile.' to '.$this->outputFile);

        return true;
    }
}
