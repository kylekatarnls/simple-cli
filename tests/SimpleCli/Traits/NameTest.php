<?php

namespace Tests\SimpleCli\Trait;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Trait\Name
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
