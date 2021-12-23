<?php

namespace Tests\SimpleCli\Attribute;

use Tests\SimpleCli\DemoApp\AttributeDemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Attribute\Argument
 */
class ArgumentTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::extractArgumentInfo
     */
    public function testHybrid(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31mA property cannot be both #Option / @option and #Argument / @argument[ESCAPE][0m',
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'bad');
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::extractArgumentInfo
     */
    public function testUse(): void
    {
        static::assertOutputContains(
            "1;30      [ESCAPE][1;30mmyText\n[ESCAPE][0mblue",
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'palette', 'myText');
            },
        );

        static::assertOutputContains(
            "1;30      [ESCAPE][1;30mHello world!\n[ESCAPE][0mblue",
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'palette');
            },
        );
    }
}
