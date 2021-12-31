<?php

declare(strict_types=1);

namespace SimpleCli\Exception;

use InvalidArgumentException as InvalidArgumentExceptionBase;

class InvalidArgumentException extends InvalidArgumentExceptionBase
{
    // Validation
    public const FAILED_VALIDATION = 0x01_01_01_01;
    public const MANDATORY_PROPERTY = 0x01_01_01_02;

    // Arguments
    public const INVALID_NUMBER_OF_ARGUMENTS = 0x01_02_01_01;

    // Options
    public const UNKNOWN_OPTION = 0x01_03_01_01;
    public const OPTION_NOT_BOOLEAN = 0x01_03_01_02;

    // Parameters
    public const INVALID_VALUE = 0x01_04_01_01;
    public const UNABLE_TO_CAST = 0x01_04_01_02;

    // Documentation
    public const DUPLICATE_ATTRIBUTE = 0x01_05_01_01;
    public const ATTRIBUTE_CONFLICT = 0x01_05_01_02;

    // Table widget
    public const UNABLE_TO_PARSE_TABLE_TEMPLATE = 0x02_01_01_01;
}
