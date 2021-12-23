<?php

namespace Tests\SimpleCli\Attribute;

use Tests\SimpleCli\DemoApp\AttributeDemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Attribute\Rest
 */
class RestTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::getRestTypeAndDescription
     */
    public function testRest(): void
    {
        static::assertOutput(
            "9\na|bbb|ccccccc\n",
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'all', 'a', 'bbb', 'ccccccc');
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::getRestTypeAndDescription
     */
    public function testHelp(): void
    {
        static::assertOutput(
            <<<EOS
            [ESCAPE][0;33mUsage:
            [ESCAPE][0m  file all [options] [<...all>]

            [ESCAPE][0;33mArguments:
            [ESCAPE][0m  [ESCAPE][0;32mall[ESCAPE][0m         All arguments
                          [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

            [ESCAPE][0;33mOptions:
            [ESCAPE][0m  [ESCAPE][0;32m-f, --foo[ESCAPE][0m   bar, biz, X, Y
                          [ESCAPE][0;36mint             [ESCAPE][0m[ESCAPE][0;33mdefault: 9[ESCAPE][0m
              [ESCAPE][0;32m-h, --help[ESCAPE][0m  Display documentation of the current command.
                          [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m

            EOS,
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'all', '-h');
            },
        );

        static::assertOutput(
            <<<EOS
            [ESCAPE][0;33mUsage:
            [ESCAPE][0m  file all-only [options] [<...all>]

            [ESCAPE][0;33mArguments:
            [ESCAPE][0m  [ESCAPE][0;32mall[ESCAPE][0m         All arguments
                          [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

            [ESCAPE][0;33mOptions:
            [ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m  Display documentation of the current command.
                          [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m

            EOS,
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'all-only', '-h');
            },
        );
    }
}
