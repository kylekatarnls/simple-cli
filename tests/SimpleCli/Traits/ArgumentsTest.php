<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Arguments
 */
class ArgumentsTest extends TestCase
{
    /**
     * @covers ::getArguments
     */
    public function testGetArguments()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([], $command->getArguments());

        ob_start();
        $command('file', 'foobar', 'My sentence');
        ob_end_clean();

        static::assertSame([
            'sentence' => 'My sentence',
        ], $command->getArguments());
    }

    /**
     * @covers ::getExpectedArguments
     */
    public function testGetExpectedArguments()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'version');
        ob_end_clean();

        static::assertSame([], $command->getExpectedArguments());

        ob_start();
        $command('file', 'foobar');
        ob_end_clean();

        static::assertSame([
            [
                'property'    => 'sentence',
                'description' => 'Sentence to display.',
                'values'      => null,
                'type'        => 'string',
            ],
        ], $command->getExpectedArguments());
    }

    /**
     * @covers ::getRestArguments
     */
    public function testGetRestArguments()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'foobar', 'My sentence', 'A', 'B');
        ob_end_clean();

        static::assertSame([], $command->getRestArguments());

        ob_start();
        $command('file', 'rest', 'My sentence', 'A', 'B');
        ob_end_clean();

        static::assertSame(['A', 'B'], $command->getRestArguments());
    }

    /**
     * @covers ::getExpectedRestArgument
     */
    public function testGetExpectedRestArgument()
    {
        $command = new DemoCli();

        ob_start();
        $command('file', 'version');
        ob_end_clean();

        static::assertNull($command->getExpectedRestArgument());

        ob_start();
        $command('file', 'rest');
        ob_end_clean();

        static::assertSame([
            'property'    => 'suffixes',
            'description' => 'Suffixes after the sentence.',
            'values'      => null,
            'type'        => 'string',
        ], $command->getExpectedRestArgument());
    }

    /**
     * @covers ::parseArgument
     */
    public function testParseArgument()
    {
        $command = new DemoCli();
        $command->disableColors();

        ob_start();
        $command('file', 'version', 'too-argument');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame('Expect only 0 arguments', $contents);

        ob_start();
        $command('file', 'rest', 'Hello', ' world', '!');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame("Hello world!\n", $contents);
    }
}
