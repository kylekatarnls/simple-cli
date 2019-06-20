<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

trait File
{
    /**
     * @var string
     */
    protected $file = null;

    /**
     * Get the current program file called from the CLI.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
}
