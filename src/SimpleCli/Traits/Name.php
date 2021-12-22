<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

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
