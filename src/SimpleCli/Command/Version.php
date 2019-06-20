<?php

declare(strict_types=1);

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Get the current version of the package providing this command line.
 */
class Version implements Command
{
    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine($cli->getVersion());

        return true;
    }
}
