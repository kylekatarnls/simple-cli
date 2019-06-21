<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DummyCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Name
 */
class NameTest extends TestCase
{
    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $command = new DemoCli();

        static::assertNull($command->getName());

        $command = new DummyCli();

        static::assertSame('stupid', $command->getName());
    }
}
