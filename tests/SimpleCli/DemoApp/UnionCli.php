<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;

class UnionCli extends SimpleCli
{
    protected string $escapeCharacter = '[ESCAPE]';

    /**
     * @return array<string, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'union' => UnionTypeCommand::class,
        ];
    }
}
