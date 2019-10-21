<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Input
 */
class InputTest extends TraitsTestCase
{
    /**
     * @covers ::recordAutocomplete
     */
    public function testRecordAutocomplete()
    {
        if (!extension_loaded('readline') || !function_exists('readline_completion_function')) {
            self::markTestSkipped('readline extension required for this test');
        }

        $command = new DemoCli();

        $command::$registered = [];

        static::invoke($command, 'recordAutocomplete');

        static::assertSame([[$command, 'autocomplete']], $command::$registered);

        $command->setReadlineCompletionExtensions(['this-extension-does-not-exist']);

        $command::$registered = [];

        static::invoke($command, 'recordAutocomplete');

        static::assertSame([], $command::$registered);
    }

    /**
     * @covers ::autocomplete
     */
    public function testAutocomplete()
    {
        $command = new DemoCli();

        $command->setAnswerer(
            function ($question) {
                if ($question === 'Are you mad?') {
                    return 'yes';
                }

                return '42';
            }
        );

        $command->read('Answer to the Ultimate Question of Life, the Universe, and Everything', ['foo', 'bar', 'biz']);

        static::assertSame(['bar', 'biz'], $command->autocomplete('b'));

        $command->read(
            'Are you mad?',
            function ($start) {
                return [
                    "$start??",
                    '42',
                ];
            }
        );

        static::assertSame(['b??', '42'], $command->autocomplete('b'));
    }

    /**
     * @covers ::read
     */
    public function testRead()
    {
        $command = new DemoCli();

        $command->setAnswerer(
            function ($question) {
                if ($question === 'Are you mad?') {
                    return 'yes';
                }

                return '42';
            }
        );

        $answer = $command->read('Answer to the Ultimate Question of Life, the Universe, and Everything');

        static::assertSame('42', $answer);

        $answer = $command->read('Are you mad?');

        static::assertSame('yes', $answer);
    }
}
