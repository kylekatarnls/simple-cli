<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\SimpleCliCommand\TypeHint;

/**
 * @coversDefaultClass \SimpleCli\Traits\Documentation
 */
class DocumentationTypeHintTest extends TraitsTestCase
{
    /**
     * @covers ::getPropertyType
     * @covers ::getPropertyTypeByHint
     */
    public function testPropertyTypeByHint(): void
    {
        if (version_compare(PHP_VERSION, '7.4.0-dev', '<')) {
            $this->markTestSkipped('Properties can be typed by hint only with PHP 7.4');
        }

        $command = new DemoCli();

        $this->invoke($command, 'extractExpectations', new TypeHint());

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }
}
