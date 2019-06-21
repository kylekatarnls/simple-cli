<?php

namespace Tests\SimpleCli\Traits;

use InvalidArgumentException;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DummyCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Options
 */
class OptionsTest extends TestCase
{
    /**
     * @covers ::getOptions
     */
    public function testGetOptions()
    {
        $command = new DummyCli();

        ob_start();
        $command('file');
        ob_end_clean();

        static::assertSame([], $command->getOptions());

        $command = new DemoCli();

        ob_start();
        $command('file');
        ob_end_clean();

        static::assertSame([], $command->getOptions());

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([], $command->getOptions());
    }

    /**
     * @covers ::getExpectedOptions
     */
    public function testGetExpectedOptions()
    {
        $command = new DummyCli();

        ob_start();
        $command('file');
        ob_end_clean();

        static::assertSame([], $command->getExpectedOptions());

        $command = new DemoCli();

        ob_start();
        $command('file');
        ob_end_clean();

        static::assertSame([], $command->getExpectedOptions());

        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([
            [
                'property'    => 'prefix',
                'names'       => [
                    'prefix',
                    'p',
                ],
                'description' => 'Append a prefix to $sentence.',
                'values'      => 'hello, hi, bye',
                'type'        => 'string',
            ],
            [
                'property'    => 'verbose',
                'names'       => [
                    'verbose',
                    'v',
                ],
                'description' => 'If this option is set, extra debug information will be displayed.',
                'values'      => null,
                'type'        => 'bool',
            ],
            [
                'property'    => 'help',
                'names'       => [
                    'help',
                    'h',
                ],
                'description' => 'Display documentation of the current command.',
                'values'      => null,
                'type'        => 'bool',
            ],
        ], $command->getExpectedOptions());
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testGetOptionDefinition()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([
            'property'    => 'prefix',
            'names'       => [
                'prefix',
                'p',
            ],
            'description' => 'Append a prefix to $sentence.',
            'values'      => 'hello, hi, bye',
            'type'        => 'string',
        ], $command->getOptionDefinition('prefix'));
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testUnknownOptionName()
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Unknown --xyz option');

        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        $command->getOptionDefinition('xyz');
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testUnknownOptionAlias()
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Unknown -x option');

        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        $command->getOptionDefinition('x');
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOption()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '-h');
        ob_end_clean();

        static::assertSame([
            'help' => true,
        ], $command->getOptions());
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOptionOnNonBoolean()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar', '-p');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame('[ESCAPE][0;31m-p option is not a boolean, so you can\'t use it in a aliases group[ESCAPE][0m', $contents);
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOptionWithValue()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar', '-h=yoh');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame('[ESCAPE][0;31m-h option is boolean and should not have value[ESCAPE][0m', $contents);
    }

    /**
     * @covers ::setOption
     */
    public function testSetOption()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar', '-p=hello');
        ob_end_clean();

        static::assertSame([
            'prefix' => 'hello',
        ], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '--help', '--prefix', 'hello');
        ob_end_clean();

        static::assertSame([
            'help'   => true,
            'prefix' => 'hello',
        ], $command->getOptions());
    }

    /**
     * @covers ::parseOption
     */
    public function testParseOption()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar', '-prefix=hello');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame('[ESCAPE][0;31mUnable to parse -prefix=hello, maybe you would mean --prefix=hello[ESCAPE][0m', $contents);

        ob_start();
        $command('file', 'foobar', '-vh');
        ob_end_clean();

        static::assertSame([
            'verbose' => true,
            'help'    => true,
        ], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '-hv');
        ob_end_clean();

        static::assertSame([
            'help'    => true,
            'verbose' => true,
        ], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '-p=hi');
        ob_end_clean();

        static::assertSame([
            'prefix' => 'hi',
        ], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '--prefix=bye');
        ob_end_clean();

        static::assertSame([
            'prefix' => 'bye',
        ], $command->getOptions());

        ob_start();
        $command('file', 'foobar', '--prefix', 'bye');
        ob_end_clean();

        static::assertSame([
            'prefix' => 'bye',
        ], $command->getOptions());
    }
}
