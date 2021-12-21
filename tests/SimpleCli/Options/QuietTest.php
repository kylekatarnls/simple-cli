<?php

namespace Tests\SimpleCli\Options;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\SimpleCli
 */
class QuietTest extends TestCase
{
    /**
     * @covers ::__invoke
     */
    public function testIsQuiet(): void
    {
        static::assertOutput(
            '',
            function () {
                $command = new DemoCli();

                $command('file', 'create', '--quiet');
            }
        );

        static::assertOutput(
            '[ESCAPE][0;36m0 programs created.
[ESCAPE][0m',
            function () {
                $command = new DemoCli();

                $command('file', 'create');
            }
        );
    }
}
