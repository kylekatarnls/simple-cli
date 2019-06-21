<?php

namespace Tests\SimpleCli\Traits;

use stdClass;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DemoCommand;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Documentation
 */
class DocumentationTest extends TestCase
{
    /**
     * @covers ::extractClassNameDescription
     */
    public function testExtractClassNameDescription()
    {
        $command = new DemoCli();

        static::assertSame('This is a demo.', $command->extractClassNameDescription(DemoCommand::class));
        static::assertSame('stdClass', $command->extractClassNameDescription(stdClass::class));
        static::assertSame('NotFound', $command->extractClassNameDescription('NotFound'));
    }
}
