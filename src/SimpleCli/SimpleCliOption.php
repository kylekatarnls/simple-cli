<?php

declare(strict_types=1);

namespace SimpleCli;

interface SimpleCliOption
{
    public const SKIP_INI_FIX = '--simple-cli-skip-ini-fix';

    public const ALL = [
        self::SKIP_INI_FIX,
    ];
}
