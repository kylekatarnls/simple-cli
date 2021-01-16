<?php

namespace Tests\SimpleCli;

use SimpleCli\SimpleCliCommand;

/**
 * @coversDefaultClass \SimpleCli\SimpleCliCommand
 */
class SimpleCliCommandTest extends TestCase
{
    /**
     * @covers ::getPackageName
     */
    public function testGetPackageName(): void
    {
        static::assertSame('simple-cli/simple-cli', (new SimpleCliCommand())->getPackageName());
    }

    /**
     * @covers ::getCommands
     */
    public function testGetCommands(): void
    {
        static::assertSame([
            'create'  => SimpleCliCommand\Create::class,
            'palette' => SimpleCliCommand\Palette::class,
        ], (new SimpleCliCommand())->getCommands());
    }
}
