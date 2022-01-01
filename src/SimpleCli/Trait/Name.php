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

    public function getDisplayName(): string
    {
        return $this->getName() ?: $this->extractName(static::class);
    }

    protected function extractName(mixed $className): string
    {
        $parts = explode('\\', (string) $className);

        return trim(
            (string) preg_replace_callback(
                '/[A-Z]/',
                static fn (array $match) => '-'.strtolower($match[0]),
                end($parts),
            ),
            '-'
        );
    }
}
