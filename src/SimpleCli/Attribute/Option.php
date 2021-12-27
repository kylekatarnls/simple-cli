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
    /**
     * @param string|null               $description
     * @param array<string>|string|null $name
     * @param array<string>|string|null $alias
     */
    public function __construct(
        public ?string $description = null,
        public array|string|null $name = null,
        public array|string|null $alias = null,
    ) {
    }
}
