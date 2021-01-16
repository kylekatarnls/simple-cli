<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

class Cell
{
    public const ALIGN_LEFT = 'left';
    public const ALIGN_CENTER = 'center';
    public const ALIGN_RIGHT = 'right';

    public const ALIGN_TOP = 'top';
    public const ALIGN_MIDDLE = 'middle';
    public const ALIGN_BOTTOM = 'bottom';

    /** @var string|object */
    protected $content;

    /** @var string|null */
    protected $horizontalAlign;

    /** @var string|null */
    protected $verticalAlign;

    /** @var int */
    protected $colSpan = 1;

    /** @var int */
    protected $rowSpan = 1;

    /**
     * @param object|string $content         string content or object with __toString() method.
     * @param string|null   $horizontalAlign left, center or right
     * @param string|null   $verticalAlign   top, middle or bottom
     */
    public function __construct($content, ?string $horizontalAlign = null, ?string $verticalAlign = null)
    {
        $this->content = $content;
        $this->horizontalAlign = $horizontalAlign;
        $this->verticalAlign = $verticalAlign;
    }

    public function cols(int $colSpan): self
    {
        $this->colSpan = max(1, $colSpan);

        return $this;
    }

    public function getColSpan(): int
    {
        return $this->colSpan;
    }

    public function rows(int $rowSpan): self
    {
        $this->rowSpan = max(1, $rowSpan);

        return $this;
    }

    public function getRowSpan(): int
    {
        return $this->rowSpan;
    }

    public function getContent(): string
    {
        /** @psalm-suppress PossiblyInvalidCast */
        return (string) $this->content;
    }

    public function getHorizontalAlign(): ?string
    {
        return $this->horizontalAlign;
    }

    public function getVerticalAlign(): ?string
    {
        return $this->verticalAlign;
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
