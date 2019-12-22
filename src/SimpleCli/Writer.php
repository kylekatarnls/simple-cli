<?php

declare(strict_types=1);

namespace SimpleCli;

interface Writer
{
    public function write(string $text = '', string $color = null, string $background = null): void;
}
