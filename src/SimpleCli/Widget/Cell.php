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

    public function __construct($content, ?string $align = null)
    {
        $this->content = $content;
        $this->align = $align;
    }

    public function getContent(): string
    {
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
