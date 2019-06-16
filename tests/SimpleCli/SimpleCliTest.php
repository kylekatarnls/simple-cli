<?php

namespace Tests\SimpleCli;

use Tests\SimpleCli\DemoCommand\DemoCommand;

class SimpleCliTest extends TestCase
{
    public function testWrite()
    {
        $command = new DemoCommand();

        static::assertOutput('Hello world', function () use ($command) {
            $command->write('Hello world');
        });

        static::assertOutput("Hello world\n", function () use ($command) {
            $command->writeLine('Hello world');
        });
    }
}
