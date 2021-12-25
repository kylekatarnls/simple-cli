<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Arguments
 */
class ArgumentsTest extends TraitsTestCase
{
    /**
     * @covers ::getArguments
     */
    public function testGetArguments(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame([], $command->getArguments());

        $command('file', 'foobar', 'My sentence');

        static::assertSame(
            [
                'sentence' => 'My sentence',
            ],
            $command->getArguments(),
        );
    }

    /**
     * @covers ::getExpectedArguments
     */
    public function testGetExpectedArguments(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'version');

        static::assertSame([], $command->getExpectedArguments());

        $command('file', 'foobar');

        static::assertSame(
            [
                [
                    'property'    => 'sentence',
                    'description' => 'Sentence to display.',
                    'values'      => null,
                    'type'        => 'string',
                    'validation'  => [],
                ],
            ],
            $command->getExpectedArguments()
        );
    }

    /**
     * @covers ::getRestArguments
     */
    public function testGetRestArguments(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar', 'My sentence', 'A', 'B');

        static::assertSame([], $command->getRestArguments());

        $command('file', 'rest', 'My sentence', 'A', 'B');

        static::assertSame(['A', 'B'], $command->getRestArguments());
    }

    /**
     * @covers ::getExpectedRestArgument
     * @covers \SimpleCli\Traits\Documentation::addExpectation
     * @covers \SimpleCli\Traits\Documentation::extractArgumentInfo
     */
    public function testGetExpectedRestArgument(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'version');

        static::assertNull($command->getExpectedRestArgument());

        $command('file', 'rest');

        static::assertSame(
            [
                'property'    => 'suffixes',
                'description' => 'Suffixes after the sentence.',
                'values'      => null,
                'type'        => 'string',
                'validation'  => [],
            ],
            $command->getExpectedRestArgument(),
        );
    }

    /**
     * @covers ::parseArgument
     */
    public function testParseArgument(): void
    {
        $command = new DemoCli();
        $command->disableColors();

        static::assertOutput(
            'Expect only 0 arguments',
            static fn () => $command('file', 'version', 'too-argument'),
        );

        static::assertOutput(
            "Hello world!\n",
            static fn () => $command('file', 'rest', 'Hello', ' world', '!'),
        );
    }
}
