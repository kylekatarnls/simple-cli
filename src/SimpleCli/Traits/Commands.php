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
     * @psalm-suppress InvalidReturnType
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
            $commands[$this->getCommandKey($index, $command)] = $command;
        }

        return array_filter($commands, 'boolval');
    }

    /**
     * @param int|string   $index
     * @param string|false $command
     *
     * @return string
     */
    private function getCommandKey($index, $command): string
    {
        if ($command && is_int($index)) {
            return strtolower((string) preg_replace(
                '/[A-Z]/',
                '-$0',
                lcfirst((string) preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $command))
            ));
        }

        return (string) $index;
    }
}
