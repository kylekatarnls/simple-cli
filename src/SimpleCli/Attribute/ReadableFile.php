<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Ensure the given argument/option is an existing and readable file path.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ReadableFile extends Validation
{
    public function proceed(mixed &$value): ?string
    {
        if ($value !== null && !(is_string($value) && is_file($value) && is_readable($value))) {
            $export = var_export($value, true);

            return "$export is not a readable file path.";
        }

        return null;
    }
}
