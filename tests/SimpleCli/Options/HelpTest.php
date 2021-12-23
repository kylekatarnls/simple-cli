<?php

namespace Tests\SimpleCli\Options;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Options\Help
 */
class HelpTest extends TestCase
{
    /**
     * @covers ::displayHelp
     * @covers ::displayOptions
     * @covers ::displayArguments
     * @covers \SimpleCli\SimpleCli::getValueExport
     * @covers \SimpleCli\SimpleCli::displayVariable
     */
    public function testDisplayHelp(): void
    {
        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] [<...classNames>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mclassNames[ESCAPE][0m     List of program classes to convert into executable CLI programs.
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
',
            static function () {
                $command = new DemoCli();

                $command('file', 'create', '--help');
            },
        );

        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file foobar [options] [<sentence>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32msentence[ESCAPE][0m       Sentence to display.
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: \'\'[ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-p, --prefix[ESCAPE][0m   Append a prefix to $sentence.
                 [ESCAPE][0;36mhello, hi, bye  [ESCAPE][0m[ESCAPE][0;33mdefault: \'\'[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
',
            static function () {
                $command = new DemoCli();

                $command('file', 'foobar', '--help');
            },
        );

        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file hall [options] [<...all>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mall[ESCAPE][0m                   All arguments
                        [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-X, -Y, --bar, --biz[ESCAPE][0m  
                        [ESCAPE][0;36mint             [ESCAPE][0m[ESCAPE][0;33mdefault: 9[ESCAPE][0m
  [ESCAPE][0;32m-h, --help[ESCAPE][0m            Display documentation of the current command.
                        [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
',
            static function () {
                $command = new DemoCli();

                $command('file', 'hall', '--help');
            },
        );
    }
}
