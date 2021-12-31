<?php

declare(strict_types=1);

namespace SimpleCli\Exception;

use RuntimeException as RuntimeExceptionBase;

class RuntimeException extends RuntimeExceptionBase
{
    // self-update
    public const HIGHEST_RELEASE_NOT_FOUND = 0x01_01_01_01;
    public const LATEST_RELEASE_NOT_FOUND = 0x01_01_01_02;
    public const NEWEST_RELEASE_NOT_FOUND = 0x01_01_01_03;

    // Input
    public const CANT_INVOKE_BASH = 0x01_02_01_01;
}
