<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\Options\Quiet;
use SimpleCli\SimpleCli;

class AutoNamingCli extends SimpleCli
{
    /**
     * @return array<string|int, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            Quiet::class,
        ];
    }
}
