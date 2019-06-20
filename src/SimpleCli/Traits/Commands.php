<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;

trait Commands
{
    /**
     * Get the list of commands expect those provided by SimpleCli.
     *
     * @return array
     */
    public function getCommands(): array
    {
        return [];
    }

    /**
     * Get the list of commands included those provided by SimpleCli.
     *
     * @return array
     */
    public function getAvailableCommands(): array
    {
        return array_filter(array_merge([
            'list'    => Usage::class,
            'version' => Version::class,
        ], $this->getCommands()), 'boolval');
    }
}
