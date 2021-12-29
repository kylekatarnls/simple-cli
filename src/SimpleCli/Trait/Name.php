<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

trait Name
{
    protected ?string $name = null;

    /**
     * Get the name of the CLI program.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
