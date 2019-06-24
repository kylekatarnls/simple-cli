<?php

declare(strict_types=1);

namespace SimpleCli\Options;

trait Quiet
{
    /**
     * @option
     *
     * If this option is set, the command will run silently (no output).
     *
     * @var bool
     */
    public $quiet = false;

    /**
     * Return true if quiet option is enabled.
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->quiet;
    }
}
