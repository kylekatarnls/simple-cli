<?php

namespace Tests\SimpleCli\Trait;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use SimpleCli\Options\Quiet;
use Tests\SimpleCli\DemoApp\AutoNamingCli;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Trait\Commands
 */
class CommandsTest extends TraitsTestCase
{
    /**
     * @covers ::getCommands
     */
    public function testGetCommands(): void
    {
        $command = new DummyCli();

        static::assertSame([], $command->getCommands());
    }

    /**
     * @covers ::getAvailableCommands
     * @covers ::getCommandKey
     */
    public function testGetAvailableCommands(): void
    {
        $command = new DummyCli();

        static::assertSame(
            [
                'list'    => Usage::class,
                'version' => Version::class,
            ],
            $command->getAvailableCommands()
        );
    }

    /**
     * @covers ::getAvailableCommands
     * @covers ::getCommandKey
     */
    public function testGetAvailableCommandsAutoNaming(): void
    {
        $command = new AutoNamingCli();

        static::assertSame(
            [
                'list'    => Usage::class,
                'version' => Version::class,
                'quiet'   => Quiet::class,
            ],
            $command->getAvailableCommands()
        );
    }
}
