<?php

namespace Tests\SimpleCli\Traits;

use InvalidArgumentException;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Parameters
 */
class ParametersTest extends TestCase
{
    /**
     * @covers ::getParameters
     */
    public function testGetParameters()
    {
        $command = new DemoCli();
        $command->mute();

        $command('foobar');

        static::assertSame([], $command->getParameters());

        $command('foobar', 'hello', 'A', 'B', 'C');

        static::assertSame(['A', 'B', 'C'], $command->getParameters());
    }

    /**
     * @covers ::getParameterValue
     */
    public function testGetParameterValue()
    {
        $command = new DemoCli();

        static::assertSame(42, $command->getParameterValue('42', [
            'type'   => 'int',
            'values' => '42',
        ]));

        static::assertSame(42, $command->getParameterValue('42.5', [
            'type'   => 'int',
            'values' => null,
        ]));

        static::assertSame(42.5, $command->getParameterValue('42.5', [
            'type'   => 'float',
            'values' => '42.5, 1',
        ]));
    }

    /**
     * @covers ::getParameterValue
     */
    public function testGetParameterValueWrongCast()
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Cannot cast arrrg to special');

        $command = new DemoCli();

        $command->getParameterValue('arrrg', [
            'type'   => 'special',
            'values' => null,
        ]);
    }

    /**
     * @covers ::getParameterValue
     */
    public function testGetParameterValueWrongValue()
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage("The parameter myProp must be one of the following values: [42.5, 1]; '42' given.");

        $command = new DemoCli();

        $command->getParameterValue('42', [
            'property' => 'myProp',
            'type'     => 'float',
            'values'   => '42.5, 1',
        ]);
    }
}
