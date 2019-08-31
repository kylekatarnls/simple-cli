<?php

namespace Tests\SimpleCli;

use Tests\SimpleCli\DemoApp\BadCli;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\InteractiveCli;

/**
 * @coversDefaultClass \SimpleCli\SimpleCli
 */
class SimpleCliTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $command = new DemoCli();

        static::assertOutput('[ESCAPE][0;31mHello world[ESCAPE][0m', function () use ($command) {
            $command->write('Hello world', 'red');
        });

        $command = new DemoCli(['red' => 'foobar']);

        static::assertOutput('[ESCAPE][foobarmHello world[ESCAPE][0m', function () use ($command) {
            $command->write('Hello world', 'red');
        });
    }

    /**
     * @covers ::getVersionDetails
     */
    public function testGetVersionDetails()
    {
        static::assertSame('', (new DemoCli())->getVersionDetails());
    }

    /**
     * @covers ::getVersion
     */
    public function testGetVersion()
    {
        static::assertSame('[ESCAPE][0;33munknown[ESCAPE][0m', (new DemoCli())->getVersion());
    }

    /**
     * @covers ::parseParameters
     */
    public function testParseParameters()
    {
        static::assertOutput('[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] [<...classNames>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mclassNames[ESCAPE][0m     
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: NULL[ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
', function () {
            $command = new DemoCli();

            $command('file', 'create', '--help');
        });

        static::assertOutput("9\n\n", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz', '9');
        });

        static::assertOutput("[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz');
        });
    }

    /**
     * @covers ::getCommandClass
     * @covers ::getCommandClassFromName
     */
    public function testGetCommandClass()
    {
        static::assertOutput('[ESCAPE][0;31mstdClass needs to implement SimpleCli\Command[ESCAPE][0m', function () {
            $command = new BadCli();

            $command('file', 'bad');
        });

        static::assertOutput('[ESCAPE][0;31mCommand ghost not found[ESCAPE][0m', function () {
            $command = new BadCli();

            $command('file', 'ghost');
        });

        static::assertOutput("9\n\n", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz', '9');
        });
    }

    /**
     * @covers ::findClosestCommand
     * @covers ::getCommandClassFromName
     * @covers ::__invoke
     */
    public function testFindClosestCommand()
    {
        static::assertOutput(implode("\n", [
            '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
            'Do you mean [ESCAPE][1;34mall[ESCAPE][0m?',
            '9',
            '',
            '',
        ]), function () {
            $command = new InteractiveCli();
            $command->setAnswers(['y']);

            $command('file', 'ball', '--biz', '9');
        });

        static::assertOutput(implode("\n", [
            '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
            'Do you mean [ESCAPE][1;34mall[ESCAPE][0m?',
        ]), function () {
            $command = new InteractiveCli();
            $command->setAnswers(['n']);

            $command('file', 'ball', '--biz', '9');
        });

        static::assertOutput(implode("\n", [
            '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
            str_repeat('Do you mean [ESCAPE][1;34mall[ESCAPE][0m?', 2),
            '9',
            '',
            '',
        ]), function () {
            $command = new InteractiveCli();
            $command->setAnswers(['o', 'y']);

            $command('file', 'ball', '--biz', '9');
        });
    }

    /**
     * @covers ::createCommander
     */
    public function testCreateCommander()
    {
        static::assertOutput("9\n\n", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz', '9');
        });

        static::assertOutput("[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz');
        });
    }

    /**
     * @covers ::__invoke
     * @covers ::hasTraitFeatureEnabled
     */
    public function testInvoke()
    {
        static::assertOutput('[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] [<...classNames>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mclassNames[ESCAPE][0m     
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: NULL[ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
', function () {
            $command = new DemoCli();

            $command('file', 'create', '--help');
        });

        static::assertOutput("9\n\n", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz', '9');
        });

        static::assertOutput("[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m", function () {
            $command = new DemoCli();

            $command('file', 'all', '--biz');
        });

        static::assertOutput('', function () {
            $command = new DemoCli();

            $command('file', 'create', '--quiet');
        });
    }
}
