<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Ensure the given argument/option is a valid and readable file path and automatically get the content for it.
 *
 * If the argument/option is non-nullable (string) the file must exist. If it's nullable (?string or string|null),
 * then the file is optional and the value will be null if it does not exist.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class GetFileContent extends Validation
{
    public function proceed(mixed &$value): ?string
    {
        if ($value === null) {
            return null;
        }

        $readable = new ReadableFile();
        $error = $readable->proceed($value);

        if ($error) {
            return $error;
        }

        $value = file_get_contents($value) ?: null;

        return null;
    }
}
