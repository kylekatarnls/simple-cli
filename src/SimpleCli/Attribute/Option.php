<?php

declare(strict_types=1);

namespace SimpleCli\Attribute;

use Attribute;

/**
 * Option to be set with --option-name or alias like -o.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Option
{
    public function __construct(
        public array|string|null $name = null,
        public array|string|null $alias = null,
        public ?string $description = null,
    ) {
    }
}
