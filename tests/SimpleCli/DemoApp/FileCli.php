<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;

class FileCli extends SimpleCli
{
    protected string $escapeCharacter = '[ESCAPE]';

    /**
     * @return array<string, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'copy' => CopyCommand::class,
            'read' => ReadCommand::class,
            'opt'  => ReadOptionalCommand::class,
        ];
    }
}
