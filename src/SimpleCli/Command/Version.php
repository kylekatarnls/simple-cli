<?php

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

class Version implements Command
{
    public function run(SimpleCli $cli, ...$parameters): bool
    {
        $cli->writeLine($cli->getVersion());

        return true;
    }
}
