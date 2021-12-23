<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Ordered argument.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Argument
{
    public function __construct(public ?string $description = null)
    {
    }
}
