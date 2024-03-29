<?php

namespace Tests\SimpleCli\Command;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Command\Version
 */
class VersionTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRun(): void
    {
        static::assertOutput(
            "[ESCAPE][0;33munknown[ESCAPE][0m\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'version');
            },
        );
    }
}
