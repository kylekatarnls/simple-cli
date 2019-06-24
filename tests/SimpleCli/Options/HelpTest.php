<?php

namespace Tests\SimpleCli\Traits;

use SimpleCli\Composer\InstalledPackage;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Options\Help
 */
class HelpTest extends TestCase
{
    /**
     * @covers ::displayHelp
     */
    public function testDisplayHelp()
    {

        static::assertOutput('[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] 

[ESCAPE][0;33mArguments:
[ESCAPE][0m
[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
', function () {
            $command = new DemoCli();

            $command('file', 'create', '--help');
        });
    }
}
