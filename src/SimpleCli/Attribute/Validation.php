<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

abstract class Validation
{
    abstract public function proceed(mixed &$value): ?string;
}
