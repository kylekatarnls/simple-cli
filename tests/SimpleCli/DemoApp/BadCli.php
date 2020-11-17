<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;
use stdClass;

class BadCli extends SimpleCli
{
    /** @var string */
    protected $escapeCharacter = '[ESCAPE]';

    /**
     * @return array<string, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'bad' => stdClass::class,
        ];
    }
}
