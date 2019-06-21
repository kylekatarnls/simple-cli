<?php

namespace Tests\SimpleCli;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Commands
 */
class CommandsTest extends TestCase
{
    /**
     * @covers ::getCommands
     */
    public function testGetCommands()
    {
        $command = new DummyCli();

        static::assertSame([], $command->getCommands());
    }

    /**
     * @covers ::getAvailableCommands
     */
    public function testGetAvailableCommands()
    {
        $command = new DummyCli();

        static::assertSame([
            'list'    => Usage::class,
            'version' => Version::class,
        ], $command->getAvailableCommands());
    }
}
