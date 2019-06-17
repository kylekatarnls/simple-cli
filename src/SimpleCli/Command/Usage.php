<?php

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

class Usage implements Command
{
    public function getDescription(): string
    {
        return 'Get the list of available commands in this program.';
    }

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
