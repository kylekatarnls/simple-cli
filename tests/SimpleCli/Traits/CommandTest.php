<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Command
 */
class CommandTest extends TraitsTestCase
{
    /**
     * @covers ::getCommand
     */
    public function testGetCommand()
    {
        $command = new DemoCli();
        $command->mute();

        $command('foobar', 'foobar');

        static::assertSame('foobar', $command->getCommand());
    }
}
