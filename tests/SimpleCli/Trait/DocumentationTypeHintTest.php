<?php

namespace Tests\SimpleCli\Trait;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\SimpleCliCommand\TypeHint;

/**
 * @coversDefaultClass \SimpleCli\Trait\Documentation
 */
class DocumentationTypeHintTest extends TraitsTestCase
{
    /**
     * @covers ::getPropertyType
     * @covers ::getTypeAndRestDescription
     * @covers ::getTypeName
     */
    public function testPropertyTypeByHint(): void
    {
        $command = new DemoCli();

        $this->invoke($command, 'extractExpectations', new TypeHint());

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }
}
