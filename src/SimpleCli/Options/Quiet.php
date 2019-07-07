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
     * @param \SimpleCli\Command $commander
     *
     * @return bool
     */
    public static function isQuiet($commander): bool
    {
        return in_array(self::class, class_uses($commander)) &&
            (/** @var self $quieter */ $quieter = $commander) &&
            $quieter->quiet ?? false;
    }
}
