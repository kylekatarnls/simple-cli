<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;
use SimpleCli\SimpleCliCommand\Create;
use SimpleCli\SimpleCliCommand\Palette;

class AttributeDemoCli extends SimpleCli
{
    protected string $escapeCharacter = '[ESCAPE]';

    /**
     * @return array<string, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'all'      => ArrayRestAttributeCommand::class,
            'all-only' => ArrayRestAttributeOnlyCommand::class,
            'bad'      => BadAttributeCommand::class,
            'create'   => Create::class,
            'rest'     => RestCommand::class,
            'foobar'   => DemoCommand::class,
            'two'      => TwoOptionsAttributeCommand::class,
            'defaults' => AutoTypeDefaultsCommand::class,
            'palette'  => Palette::class,
            'val'      => ValuesAttributeCommand::class,
        ];
    }
}
