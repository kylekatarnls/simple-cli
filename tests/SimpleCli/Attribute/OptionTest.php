<?php

namespace Tests\SimpleCli\Attribute;

use SimpleCli\Attribute\Option;
use Tests\SimpleCli\DemoApp\AttributeDemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Attribute\Option
 */
class OptionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::getAttributeOrAnnotation
     */
    public function testTwoOptions(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31mOnly 1 attribute of '.
            Option::class.
            ' can be set on a given property.[ESCAPE][0m',
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'two');
            },
        );
    }

    /**
     * @covers ::__construct
     */
    public function testHelp(): void
    {
        static::assertOutput(
            <<<'EOS'
            [ESCAPE][0;33mUsage:
            [ESCAPE][0m  file val [options] 

            [ESCAPE][0;33mOptions:
            [ESCAPE][0m  [ESCAPE][0;32m-l, --level[ESCAPE][0m  First option
                           [ESCAPE][0;36mlow, medium, high[ESCAPE][0m[ESCAPE][0;33mdefault: 'low'[ESCAPE][0m
              [ESCAPE][0;32m-h, --help[ESCAPE][0m   Display documentation of the current command.
                           [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m

            EOS,
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'val', '--help');
            },
        );
    }
}
