<?php

namespace Tests\SimpleCli\Attribute;

use Tests\SimpleCli\DemoApp\AttributeDemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Attribute\Values
 */
class ValuesTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers \SimpleCli\Traits\Documentation::getValues
     */
    public function testInvalidValue(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31mThe parameter level must be one of the following values: '.
            "[low, medium, high]; 'over-high' given.[ESCAPE][0m",
            static function () {
                $command = new AttributeDemoCli();

                $command('file', 'val', '--level', 'over-high');
            },
        );
    }
}
