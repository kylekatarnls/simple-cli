<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Options\Quiet
 */
class HelpTest extends TestCase
{
    /**
     * @covers ::isQuiet
     */
    public function testDisplayHelp()
    {
        static::assertOutput('', function () {
            $command = new DemoCli();

            $command('file', 'create', '--quiet');
        });

        static::assertOutput('[ESCAPE][0;36m0 programs created.
[ESCAPE][0m', function () {
            $command = new DemoCli();

            $command('file', 'create');
        });
    }
}
