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
     * @internal
     *
     * @param self|\SimpleCli\Command $commander
     *
     * @return bool
     */
    public static function isQuiet($commander): bool
    {
        return in_array(self::class, class_uses($commander)) && $commander->quiet ?? false;
    }
}
