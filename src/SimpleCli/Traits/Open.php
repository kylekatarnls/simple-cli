<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

trait Open
{
    public function open(string $path): void
    {
        shell_exec(
            (preg_match('/^win/i', PHP_OS) ? 'start' : 'xdg-open').
            " $path",
        );
    }
}
