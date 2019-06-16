<?php

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

class Usage implements Command
{
    public function run(SimpleCli $cli, ...$parameters): bool
    {
        $cli->write('ok');

        return $parameters[0] !== 'error';
    }
}
