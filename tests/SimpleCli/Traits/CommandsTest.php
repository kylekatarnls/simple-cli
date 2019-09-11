<?php

namespace Tests\SimpleCli\Traits;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use SimpleCli\Options\Quiet;
use Tests\SimpleCli\DemoApp\AutoNamingCli;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Commands
 */
class CommandsTest extends TraitsTestCase
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

    /**
     * @covers ::getAvailableCommands
     */
    public function testGetAvailableCommandsAutoNaming()
    {
        $command = new AutoNamingCli();

        static::assertSame([
            'list'    => Usage::class,
            'version' => Version::class,
            'quiet'   => Quiet::class,
        ], $command->getAvailableCommands());
    }
}
