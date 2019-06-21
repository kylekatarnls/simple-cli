<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Command
 */
class CommandTest extends TestCase
{
    /**
     * @covers ::getCommand
     */
    public function testGetCommand()
    {
        $command = new DemoCli();

        ob_start();
        $command('foobar', 'hello');
        ob_end_clean();

        static::assertSame('hello', $command->getCommand());
    }
}
