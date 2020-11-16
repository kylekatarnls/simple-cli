<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use SimpleCli\Command;
use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;

trait Commands
{
    /**
     * Get the list of commands expect those provided by SimpleCli.
     *
     * @return array<int|string, class-string<Command>|false>
     */
    public function getCommands(): array
    {
        return [];
    }

    /**
     * Get the list of commands included those provided by SimpleCli.
     *
     * @return array<string, class-string<Command>>
     */
    public function getAvailableCommands(): array
    {
        $commands = [
            'list'    => Usage::class,
            'version' => Version::class,
        ];

        foreach ($this->getCommands() as $index => $command) {
            if (!$command) {
                continue;
            }

            if (is_int($index)) {
                $index = (string) preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $command);
                $index = strtolower((string) preg_replace('/[A-Z]/', '-$0', lcfirst($index)));
            }

            $commands[$index] = $command;
        }

        return $commands;
    }
}
