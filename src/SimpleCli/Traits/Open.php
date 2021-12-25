<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

trait Open
{
    public function open(string $path): void
    {
        // @phan-suppress-next-line PhanUndeclaredProperty
        ($this->execFunction ?? 'shell_exec')(
            (preg_match('/^win/i', PHP_OS) ? 'start' : 'xdg-open').
            " $path",
        );
    }
}
