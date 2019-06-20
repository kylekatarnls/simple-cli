<?php

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Get the current version of the package providing this command line.
 */
class Version implements Command
{
    public function run(SimpleCli $cli, ...$parameters): bool
    {
        $cli->writeLine($cli->getVersion());

        return true;
    }
}
