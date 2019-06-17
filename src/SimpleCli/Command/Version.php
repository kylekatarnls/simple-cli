<?php

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

class Version implements Command
{
    public function getDescription(): string
    {
        return 'Get the current version of the package providing this command line.';
    }

    public function run(SimpleCli $cli, ...$parameters): bool
    {
        $cli->writeLine($cli->getVersion());

        return true;
    }
}
