<?php

namespace Tests\SimpleCli\Command;

use SimpleCli\Command\Usage;
use SimpleCli\SimpleCli;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Command\Usage
 */
class UsageTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRun(): void
    {
        static::assertOutput(
            '[ESCAPE][0;33mUsage:
[ESCAPE][0m  file [command] [options] [arguments]

[ESCAPE][0;33mAvailable commands:
[ESCAPE][0m  [ESCAPE][0;32mlist[ESCAPE][0m     Get the list of available commands in this program.
  [ESCAPE][0;32mversion[ESCAPE][0m  Get the current version of the package providing this command line.
  [ESCAPE][0;32mall[ESCAPE][0m      This is a demo.
  [ESCAPE][0;32mhall[ESCAPE][0m     This is a demo.
  [ESCAPE][0;32mbad[ESCAPE][0m      Invalid command.
  [ESCAPE][0;32mcreate[ESCAPE][0m   Create a program in the bin directory that call the class given as argument.
           Argument should be a class name (with namespace) that extends SimpleCli\\SimpleCli.
           Note that you must escape it, e.g. MyNamespace\\\\MyClass.
  [ESCAPE][0;32mrest[ESCAPE][0m     This is a demo.
  [ESCAPE][0;32mfoobar[ESCAPE][0m   This is a demo.
',
            function () {
                $command = new DemoCli();

                $command('file', 'list');
            }
        );
    }

    /**
     * @covers ::run
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testCommandsEmptyList(): void
    {
        $cli = new class() extends SimpleCli {
            /** @var string */
            public $output = '';

            public function getFile(): string
            {
                return 'x-file';
            }

            public function getCommands(): array
            {
                return [
                    'list'    => false,
                    'palette' => false,
                    'version' => false,
                ];
            }

            public function write(string $text = '', ?string $color = null, ?string $background = null): void
            {
                $this->output .= ($color ?? '').'/'.($background ?? '').'/'.$text;
            }
        };

        $this->assertSame([], $cli->getAvailableCommands());

        $usage = new Usage();
        $usage->run($cli);

        $this->assertSame(
            "brown//Usage:\n//  x-file [command] [options] [arguments]\n//\nbrown//Available commands:\n",
            $cli->output
        );
    }
}
