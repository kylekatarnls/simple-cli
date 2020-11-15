<?php

declare(strict_types=1);

namespace SimpleCli\Options;

trait Verbose
{
    /**
     * @option
     *
     * If this option is set, extra debug information will be displayed.
     *
     * @var bool
     */
    public $verbose = false;
}
