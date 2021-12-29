<?php

namespace Tests\SimpleCli\Trait;

use Closure;
use SimpleCli\Trait\Input;
use SimpleCli\Writer;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\UnionCli;

/**
 * @coversDefaultClass \SimpleCli\Trait\Input
 */
class InputTest extends TraitsTestCase
{
    /**
     * @covers ::recordAutocomplete
     */
    public function testRecordAutocomplete(): void
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
    public function testAutocomplete(): void
    {
        $command = new DemoCli();

        $command->setAnswerer(
            function ($question) {
                if ($question === 'Are you mad?') {
                    return 'yes';
                }

                return '42';
            },
        );

        $command->read('Answer to the Ultimate Question of Life, the Universe, and Everything', ['foo', 'bar', 'biz']);

        static::assertSame(['bar', 'biz'], $command->autocomplete('b'));

        $command->read(
            'Are you mad?',
            static fn ($start) => [
                "$start??",
                '42',
            ],
        );

        static::assertSame(['b??', '42'], $command->autocomplete('b'));
    }

    /**
     * @covers ::read
     */
    public function testRead(): void
    {
        $command = new DemoCli();

        $command->setAnswerer(
            function ($question) {
                if ($question === 'Are you mad?') {
                    return 'yes';
                }

                return '42';
            },
        );

        $answer = $command->read('Answer to the Ultimate Question of Life, the Universe, and Everything');

        static::assertSame('42', $answer);

        $answer = $command->read('Are you mad?');

        static::assertSame('yes', $answer);
    }

    /**
     * @covers ::getStandardInput
     */
    public function testGetStandardInput(): void
    {
        $command = new class() extends DemoCli {
            public function setStdinStream(string $stdinStream): void
            {
                $this->stdinStream = $stdinStream;
            }
        };

        $file = tempnam(sys_get_temp_dir(), 'scli');
        $content = (string) mt_rand();
        file_put_contents($file, $content);

        $command->setStdinStream($file);
        $stdin = $command->getStandardInput();
        unlink($file);

        static::assertSame($content, $stdin);
    }

    /**
     * @covers ::displayMessage
     */
    public function testDisplayMessage(): void
    {
        $command = new class() {
            use Input;

            public function display(string $message): void
            {
                $this->displayMessage($message);
            }
        };
        ob_start();
        $command->display('Hello');
        $contents = ob_get_contents();
        ob_end_clean();

        static::assertSame('Hello', $contents);

        $command = new class() implements Writer {
            use Input;

            public array $output = [];

            public function display(string $message): void
            {
                $this->displayMessage($message);
            }

            public function write(string $text = '', string $color = null, string $background = null): void
            {
                $this->output[] = [$text, $color, $background];
            }
        };
        $command->display('Hello');

        static::assertSame([
            ['Hello', null, null],
        ], $command->output);
    }

    /**
     * @covers ::readHidden
     * @covers ::readHiddenPrompt
     */
    public function testReadHidden(): void
    {
        $commands = [
            'shell' => [],
            'bat'   => [],
        ];
        $output = null;
        $windows = preg_match('/^win/i', PHP_OS);

        static::assertOutput(
            ($windows ? 'Password:' : '').PHP_EOL,
            static function () use (&$commands, &$output) {
                $command = new class() extends DemoCli {
                    public function setExecBatFunction(array|Closure|string|null $execBatFunction): void
                    {
                        $this->execBatFunction = $execBatFunction;
                    }

                    public function setExecFunction(array|Closure|string|null $execFunction): void
                    {
                        $this->execFunction = $execFunction;
                    }
                };
                $command->setExecFunction(static function (string $command) use (&$commands) {
                    $commands['shell'][] = $command;

                    return 'OK';
                });
                $command->setExecBatFunction(static function (string $command) use (&$commands) {
                    $commands['bat'][] = $command;

                    return 'OK';
                });

                $output = $command->readHidden('Password:');
            },
        );

        static::assertSame('OK', $output);
        static::assertSame(
            $windows
                ? [
                    'shell' => [],
                    'bat'   => [
                        realpath(__DIR__.'/../../../src/SimpleCli/Traits').'/../../../bin/prompt_win.bat',
                    ],
                ]
                : [
                    'shell' => [
                        "/usr/bin/env bash -c 'echo OK'",
                        "/usr/bin/env bash -c 'read -s -p \"Password:\" secret && echo \$secret'",
                    ],
                    'bat'   => [],
                ],
            $commands,
        );
    }
}
