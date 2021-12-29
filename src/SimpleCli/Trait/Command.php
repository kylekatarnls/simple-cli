<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

trait Command
{
    /**
     * @var string
     */
    protected $command;

    /**
     * Get the selected command.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
}
