<?php

namespace SimpleCli\Traits;

trait Name
{
    /**
     * @var string|null
     */
    protected $name = null;

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
