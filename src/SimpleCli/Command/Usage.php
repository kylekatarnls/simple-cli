<?php

declare(strict_types=1);

namespace SimpleCli\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Get the list of available commands in this program.
 */
class Usage implements Command
{
    public function run(SimpleCli $cli): bool
    {
        $commands = $cli->getAvailableCommands();
        $length = max(array_map('strlen', array_keys($commands))) + 2;

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine('  '.basename($cli->getFile()).' [command] [options] [arguments]');
        $cli->writeLine();

        $cli->writeLine('Available commands:', 'brown');

        foreach ($commands as $command => $className) {
            $cli->write('  ');
            $cli->write($command, 'green');
            $cli->write(str_repeat(' ', $length - strlen($command)));
            $cli->writeLine(
                str_replace(
                    "\n",
                    "\n".str_repeat(' ', $length + 2),
                    $cli->extractClassNameDescription($className)
                )
            );
        }

        return true;
    }
}
