<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;

class DemoCli extends SimpleCli
{
    protected $escapeCharacter = '[ESCAPE]';

    public function getCommands(): array
    {
        return [];
    }
}
