<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Name
 */
class NameTest extends TraitsTestCase
{
    /**
     * @covers ::getName
     */
    public function testGetName(): void
    {
        $command = new DemoCli();

        static::assertNull($command->getName());

        $command = new DummyCli();

        static::assertSame('stupid', $command->getName());
    }
}
