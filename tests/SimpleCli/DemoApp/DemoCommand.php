<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * This is a demo.
 */
class DemoCommand implements Command
{
    /**
     * @option('--verbose', '-v')
     *
     * If this option is set, extra debug information will be displayed.
     *
     * @var bool
     */
    public $verbose = false;

    /**
     * @option('--prefix', '-p')
     *
     * Append a prefix to $sentence.
     *
     * @var string
     */
    public $prefix = '';

    /**
     * @argument
     *
     * Sentence to display.
     *
     * @var string
     */
    public $sentence = '';

    public function run(SimpleCli $cli, ...$parameters): bool
    {
        $commands = $cli->getAvailableCommands();
        $length = max(...array_map('mb_strlen', array_keys($commands))) + 2;

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine('  '.$cli->getFile().' [command] [options] [arguments]');
        $cli->writeLine();

        $cli->writeLine('Available commands:', 'brown');

        foreach ($commands as $command => $className) {
            $cli->write('  ');
            $cli->write($command, 'green');
            $cli->write(str_repeat(' ', $length - mb_strlen($command)));
            $cli->writeLine((new $className)->getDescription());
        }

        return true;
    }
}
