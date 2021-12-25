<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Ensure the given argument/option is a path where a file could be written (created or modified).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class WritableFile extends Validation
{
    public function proceed(mixed &$value): ?string
    {
        if ($value !== null && !(is_string($value) && is_writable(dirname($value)))) {
            $export = var_export($value, true);

            return "$export is not a writable file path.";
        }

        return null;
    }
}
