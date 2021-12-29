<?php

namespace Tests\SimpleCli;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use SimpleCli\SimpleCli;
use Tests\SimpleCli\DemoApp\BadCli;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\InteractiveCli;
use Tests\SimpleCli\DemoApp\TraitCommand;

/**
 * @coversDefaultClass \SimpleCli\SimpleCli
 */
class SimpleCliTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $command = new DemoCli();

        static::assertOutput(
            '[ESCAPE][0;31mHello world[ESCAPE][0m',
            function () use ($command) {
                $command->write('Hello world', 'red');
            }
        );

        $command = new DemoCli(['red' => 'foobar']);

        static::assertOutput(
            '[ESCAPE][foobarmHello world[ESCAPE][0m',
            function () use ($command) {
                $command->write('Hello world', 'red');
            }
        );
    }

    /**
     * @covers ::getVersionDetails
     */
    public function testGetVersionDetails(): void
    {
        static::assertSame('', (new DemoCli())->getVersionDetails());
    }

    /**
     * @covers ::getVersion
     */
    public function testGetVersion(): void
    {
        static::assertSame('[ESCAPE][0;33munknown[ESCAPE][0m', (new DemoCli())->getVersion());

        define('SIMPLE_CLI_PHAR_PROGRAM_VERSION', '1.2.3');

        static::assertSame('[ESCAPE][0;33m1.2.3[ESCAPE][0m', (new DemoCli())->getVersion());
    }

    /**
     * @covers ::parseParameters
     */
    public function testParseParameters(): void
    {
        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] [<...classNames>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mclassNames[ESCAPE][0m     List of program classes to convert into executable CLI programs.
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
',
            static function () {
                $command = new DemoCli();

                $command('file', 'create', '--help');
            },
        );

        static::assertOutput(
            "9\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz', '9');
            },
        );

        static::assertOutput(
            "[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz');
            },
        );
    }

    /**
     * @covers ::getCommandName
     * @covers ::getCommandClassFromName
     * @covers ::__invoke
     */
    public function testGetCommandClass(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31mstdClass needs to implement SimpleCli\Command[ESCAPE][0m',
            static function () {
                $command = new BadCli();

                $command('file', 'bad');
            },
        );

        static::assertOutput(
            '[ESCAPE][0;31mCommand ghost not found[ESCAPE][0m',
            static function () {
                $command = new BadCli();

                $command('file', 'ghost');
            },
        );

        static::assertOutput(
            "9\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz', '9');
            },
        );
    }

    /**
     * @covers ::findClosestCommand
     * @covers ::getCommandName
     * @covers ::getCommandClassFromName
     * @covers ::__invoke
     */
    public function testFindClosestCommand(): void
    {
        static::assertOutput(
            implode(
                "\n",
                [
                    '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
                    'Do you mean [ESCAPE][1;34mall[ESCAPE][0m?',
                    '9',
                    '',
                    '',
                ]
            ),
            static function () {
                $command = new InteractiveCli();
                $command->setAnswers(['y']);

                $command('file', 'ball', '--biz', '9');
            },
        );

        static::assertOutput(
            implode(
                "\n",
                [
                    '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
                    'Do you mean [ESCAPE][1;34mall[ESCAPE][0m?',
                ]
            ),
            static function () {
                $command = new InteractiveCli();
                $command->setAnswers(['n']);

                $command('file', 'ball', '--biz', '9');
            },
        );

        static::assertOutput(
            implode(
                "\n",
                [
                    '[ESCAPE][0;31mCommand ball not found[ESCAPE][0m',
                    str_repeat('Do you mean [ESCAPE][1;34mall[ESCAPE][0m?', 2),
                    '9',
                    '',
                    '',
                ]
            ),
            static function () {
                $command = new InteractiveCli();
                $command->setAnswers(['o', 'y']);

                $command('file', 'ball', '--biz', '9');
            },
        );
    }

    /**
     * @covers ::createCommander
     */
    public function testCreateCommander(): void
    {
        static::assertOutput(
            "9\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz', '9');
            },
        );

        static::assertOutput(
            "[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz');
            },
        );
    }

    /**
     * @covers ::__invoke
     * @covers ::hasTraitFeatureEnabled
     */
    public function testInvoke(): void
    {
        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file create [options] [<...classNames>]

[ESCAPE][0;33mArguments:
[ESCAPE][0m  [ESCAPE][0;32mclassNames[ESCAPE][0m     List of program classes to convert into executable CLI programs.
                 [ESCAPE][0;36mstring          [ESCAPE][0m[ESCAPE][0;33mdefault: [][ESCAPE][0m

[ESCAPE][0;33mOptions:
[ESCAPE][0m  [ESCAPE][0;32m-h, --help[ESCAPE][0m     Display documentation of the current command.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-q, --quiet[ESCAPE][0m    If this option is set, the command will run silently (no output).
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
  [ESCAPE][0;32m-v, --verbose[ESCAPE][0m  If this option is set, extra debug information will be displayed.
                 [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
',
            static function () {
                $command = new DemoCli();

                $command('file', 'create', '--help');
            },
        );

        static::assertOutput(
            "9\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz', '9');
            },
        );

        static::assertOutput(
            "[ESCAPE][0;31m--biz option is not a boolean, so you can't use it in a aliases group[ESCAPE][0m",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz');
            },
        );

        static::assertOutput(
            '',
            static function () {
                $command = new DemoCli();

                $command('file', 'create', '--quiet');
            },
        );
    }

    /**
     * @covers ::getCommandTraits
     */
    public function testGetCommandTraits(): void
    {
        static::assertSame([
            'SimpleCli\Options\Verbose' => 'SimpleCli\Options\Verbose',
            'SimpleCli\Trait\Input'     => 'SimpleCli\Trait\Input',
        ], (new InteractiveCli())->traits(TraitCommand::class));
    }

    /**
     * @covers \SimpleCli\Trait\Commands::getAvailableCommands
     * @covers \SimpleCli\Trait\Commands::getCommandKey
     */
    public function testCommandsFilter(): void
    {
        $cli = new class() extends SimpleCli {
            public function getCommands(): array
            {
                return ['version' => false];
            }
        };
        $commands = $cli->getAvailableCommands();

        $this->assertSame(['list' => Usage::class], $commands);

        $cli = new class() extends SimpleCli {
            // Noop
        };
        $commands = $cli->getAvailableCommands();

        $this->assertSame([
            'list'    => Usage::class,
            'version' => Version::class,
        ], $commands);
    }
}
