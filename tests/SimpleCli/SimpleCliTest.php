<?php

namespace Tests\SimpleCli;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\SimpleCli
 */
class SimpleCliTest extends TestCase
{
    /**
     * @covers ::write
     */
    public function testWrite()
    {
        $command = new DemoCli();

        static::assertOutput('Hello world', function () use ($command) {
            $command->write('Hello world');
        });

        static::assertOutput('[ESCAPE][0;31mHello world[ESCAPE][0m', function () use ($command) {
            $command->write('Hello world', 'red');
        });
    }

    /**
     * @covers ::writeLine
     */
    public function testWriteLine()
    {
        $command = new DemoCli();

        static::assertOutput('Hello world', function () use ($command) {
            $command->write('Hello world');
        });

        static::assertOutput("Hello world\n", function () use ($command) {
            $command->writeLine('Hello world');
        });

        static::assertOutput("[ESCAPE][0;31mHello world\n[ESCAPE][0m", function () use ($command) {
            $command->writeLine('Hello world', 'red');
        });
    }
}
