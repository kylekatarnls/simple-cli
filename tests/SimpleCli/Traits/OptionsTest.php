<?php

namespace Tests\SimpleCli\Traits;

use InvalidArgumentException;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DummyCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Options
 */
class OptionsTest extends TraitsTestCase
{
    /**
     * @covers ::getOptions
     */
    public function testGetOptions(): void
    {
        $command = new DummyCli();
        $command->mute();

        $command('file');

        static::assertSame([], $command->getOptions());

        $command = new DemoCli();
        $command->mute();

        $command('file');

        static::assertSame([], $command->getOptions());

        $command('file', 'foobar');

        static::assertSame([], $command->getOptions());
    }

    /**
     * @covers ::getExpectedOptions
     * @covers \SimpleCli\Traits\Documentation::getValues
     */
    public function testGetExpectedOptions(): void
    {
        $command = new DummyCli();
        $command->mute();

        $command('file');

        static::assertSame([], $command->getExpectedOptions());

        $command = new DemoCli();
        $command->mute();

        $command('file');

        static::assertSame([], $command->getExpectedOptions());

        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            [
                [
                    'property'    => 'prefix',
                    'names'       => [
                        'prefix',
                        'p',
                    ],
                    'description' => 'Append a prefix to $sentence.',
                    'values'      => ['hello', 'hi', 'bye'],
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
            ],
            $command->getExpectedOptions(),
        );
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testGetOptionDefinition(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            [
                'property'    => 'prefix',
                'names'       => ['prefix', 'p'],
                'description' => 'Append a prefix to $sentence.',
                'values'      => ['hello', 'hi', 'bye'],
                'type'        => 'string',
            ],
            $command->getOptionDefinition('prefix'),
        );
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testUnknownOptionName(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Unknown --xyz option');

        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        $command->getOptionDefinition('xyz');
    }

    /**
     * @covers ::getOptionDefinition
     */
    public function testUnknownOptionAlias(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Unknown -x option');

        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        $command->getOptionDefinition('x');
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOption(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame([], $command->getOptions());

        $command('file', 'foobar', '-h');

        static::assertSame(
            [
                'help' => true,
            ],
            $command->getOptions(),
        );
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOptionOnNonBoolean(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31m-p option is not a boolean, so you can\'t use it in a aliases group[ESCAPE][0m',
            function () {
                $command = new DemoCli();

                $command('file', 'foobar', '-p');
            }
        );
    }

    /**
     * @covers ::enableBooleanOption
     */
    public function testEnableBooleanOptionWithValue(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31m-h option is boolean and should not have value[ESCAPE][0m',
            function () {
                $command = new DemoCli();

                $command('file', 'foobar', '-h=yoh');
            }
        );
    }

    /**
     * @covers ::setOption
     */
    public function testSetOption(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar', '-p=hello');

        static::assertSame(
            [
                'prefix' => 'hello',
            ],
            $command->getOptions(),
        );

        $command('file', 'foobar', '--help', '--prefix', 'hello');

        static::assertSame(
            [
                'help'   => true,
                'prefix' => 'hello',
            ],
            $command->getOptions(),
        );
    }

    /**
     * @covers ::parseOption
     */
    public function testParseOption(): void
    {
        $command = new DemoCli();

        static::assertOutput(
            '[ESCAPE][0;31mUnable to parse -prefix=hello, maybe you would mean --prefix=hello[ESCAPE][0m',
            function () use ($command) {
                $command('file', 'foobar', '-prefix=hello');
            }
        );

        $command->mute();

        $command('file', 'foobar', '-vh');

        static::assertSame(
            [
                'verbose' => true,
                'help'    => true,
            ],
            $command->getOptions(),
        );

        $command('file', 'foobar', '-hv');

        static::assertSame(
            [
                'help'    => true,
                'verbose' => true,
            ],
            $command->getOptions(),
        );

        $command('file', 'foobar', '-p=hi');

        static::assertSame(
            [
                'prefix' => 'hi',
            ],
            $command->getOptions(),
        );

        $command('file', 'foobar', '--prefix=bye');

        static::assertSame(
            [
                'prefix' => 'bye',
            ],
            $command->getOptions(),
        );

        $command('file', 'foobar', '--prefix', 'bye');

        static::assertSame(
            [
                'prefix' => 'bye',
            ],
            $command->getOptions(),
        );
    }
}
