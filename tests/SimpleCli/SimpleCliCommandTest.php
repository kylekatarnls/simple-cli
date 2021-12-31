<?php

namespace Tests\SimpleCli;

use SimpleCli\Command\SelfUpdate;
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
            'build-phar'  => SimpleCliCommand\BuildPhar::class,
            'create'      => SimpleCliCommand\Create::class,
            'palette'     => SimpleCliCommand\Palette::class,
            'self-update' => SelfUpdate::class,
        ], (new SimpleCliCommand())->getCommands());
    }
}
