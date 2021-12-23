<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Remaining arguments after all specific arguments filled.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Rest
{
    public function __construct(public ?string $description = null)
    {
    }
}
