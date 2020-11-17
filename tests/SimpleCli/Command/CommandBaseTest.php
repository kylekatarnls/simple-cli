<?php

namespace Tests\SimpleCli\Command;

use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;
use Tests\SimpleCli\TestCase;

class CommandBaseTest extends TestCase
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testRun(): void
    {
        self::assertTrue(method_exists(new class() extends CommandBase {
            public function run(SimpleCli $cli): bool
            {
                return true;
            }
        }, 'displayHelp'));
    }
}
