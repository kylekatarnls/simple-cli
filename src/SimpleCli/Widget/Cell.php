<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

class Cell
{
    public const ALIGN_LEFT = 'left';
    public const ALIGN_CENTER = 'center';
    public const ALIGN_RIGHT = 'right';

    /** @var string|object */
    protected $content;

    /** @var string|null */
    protected $align;

    /** @var int */
    protected $colSpan = 1;

    /**
     * @param object|string $content string content or object with __toString() method.
     * @param string|null   $align   left, center or right
     */
    public function __construct($content, ?string $align = null)
    {
        $this->content = $content;
        $this->align = $align;
    }

    public function cols(int $colSpan): self
    {
        $this->colSpan = (int) max(1, $colSpan);

        return $this;
    }

    public function getColSpan(): int
    {
        return $this->colSpan;
    }

    public function getContent(): string
    {
        /** @psalm-suppress PossiblyInvalidCast */
        return (string) $this->content;
    }

    public function getAlign(): ?string
    {
        return $this->align;
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
