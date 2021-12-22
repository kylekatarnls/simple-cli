<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * List of possible values.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Values
{
    /** @param string[] $values */
    public function __construct(public array $values)
    {
    }
}
