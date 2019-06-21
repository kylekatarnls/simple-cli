<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Output
 */
class OutputTest extends TestCase
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
     * @covers ::colorize
     * @covers ::getColorCode
     */
    public function testColorize()
    {
        $command = new DemoCli();

        static::assertSame('Hello world', $command->colorize('Hello world'));
        static::assertSame('Hello world', $command->colorize('Hello world', null, null));
        static::assertSame('[ESCAPE][41mHello world[ESCAPE][0m', $command->colorize('Hello world', null, 'red'));
        static::assertSame('[ESCAPE][0;34mHello world[ESCAPE][0m', $command->colorize('Hello world', 'blue'));
        static::assertSame('[ESCAPE][0;34m[ESCAPE][43mHello world[ESCAPE][0m', $command->colorize('Hello world', 'blue', 'yellow'));
    }

    /**
     * @covers ::setEscapeCharacter
     */
    public function testSetEscapeCharacter()
    {
        $command = new DemoCli();

        static::assertSame('[ESCAPE][41mHello world[ESCAPE][0m', $command->colorize('Hello world', null, 'red'));

        $command->setEscapeCharacter('#');

        static::assertSame('#[41mHello world#[0m', $command->colorize('Hello world', null, 'red'));
    }

    /**
     * @covers ::setColors
     */
    public function testSetColors()
    {
        $command = new DemoCli();

        static::assertSame('[ESCAPE][0;31m[ESCAPE][41mHello world[ESCAPE][0m', $command->colorize('Hello world', 'red', 'red'));

        $command->setColors([
            'red' => 'ab',
        ], [
            'red' => 'xy',
        ]);

        static::assertSame('[ESCAPE][abm[ESCAPE][xymHello world[ESCAPE][0m', $command->colorize('Hello world', 'red', 'red'));
    }

    /**
     * @covers ::rewind
     */
    public function testRewind()
    {
        $command = new DemoCli();

        static::assertOutput('Hello world[ESCAPE][11D[ESCAPE][3D', function () use ($command) {
            $command->write('Hello world');
            $command->rewind();
            $command->rewind(3);
        });
    }

    /**
     * @covers ::rewrite
     */
    public function testRewrite()
    {
        $command = new DemoCli();

        static::assertOutput('Hello world[ESCAPE][11DBye', function () use ($command) {
            $command->write('Hello world');
            $command->rewrite('Bye');
        });
    }

    /**
     * @covers ::rewriteLine
     */
    public function testRewriteLine()
    {
        $command = new DemoCli();

        static::assertOutput("Hello world\n\rBye", function () use ($command) {
            $command->writeLine('Hello world');
            $command->rewriteLine('Bye');
        });
    }
}
