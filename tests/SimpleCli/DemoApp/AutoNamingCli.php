<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Options\Quiet;
use SimpleCli\SimpleCli;

class AutoNamingCli extends SimpleCli
{
    public function getCommands(): array
    {
        return [
            Quiet::class,
        ];
    }
}
