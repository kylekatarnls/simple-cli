<?php

namespace Tests\SimpleCli\Trait;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Trait\Command
 */
class CommandTest extends TraitsTestCase
{
    /**
     * @covers ::getCommand
     */
    public function testGetCommand(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('foobar', 'foobar');

        static::assertSame('foobar', $command->getCommand());
    }
}
