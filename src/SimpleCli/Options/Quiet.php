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
}
