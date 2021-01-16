<?php

declare(strict_types=1);

namespace SimpleCli;

use Stringable;

class EnhancedString implements Stringable, Color, Background
{
    /** @var Stringable|string */
    protected $string;

    /** @var string|null */
    protected $color;

    /** @var string|null */
    protected $background;

    /** @var CliConfig|null */
    protected $config;

    /**
     * @param Stringable|string $string
     */
    public function __construct($string, ?string $color = null, ?string $background = null, CliConfig $config = null)
    {
        $this->string = $string;
        $this->color = $color;
        $this->background = $background;
        $this->config = $config;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    protected function getColorCodeRegex(string $delimiter = '', bool $capture = false): string
    {
        static $cache = [];

        $code = $this->config->getColorCodePattern();
        $escape = $this->config->getEscapeCharacterRegex();
        $key = "$capture\n$delimiter\n$code\$escape";

        if (!isset($cache[$key])) {
            $innerCode = '[0-9;]+';

            if ($capture) {
                $innerCode = "($innerCode)";
            }

            $cache[$key] = sprintf(
                sprintf('%s%s%s', $delimiter, preg_quote($code, $delimiter), $delimiter),
                $escape,
                $innerCode
            );
        }

        return $cache[$key];
    }

    public function getLength(): int
    {
        $string = (string) $this;

        if ($this->config) {
            $string = preg_replace('/'.$this->getColorCodeRegex().'/u', '', $string);
        }

        return mb_strlen($string);
    }

    public function getSubString(string $text, int $offset, ?int $length = null): string
    {
        $multiplier = $length === null ? '*' : "{{$length}}";
        $textRegex = sprintf(
            '(?:(?:%s)*[^%s])',
            $this->getColorCodeRegex(),
            $this->config->getEscapeCharacterRegex()
        );

        return preg_replace(
            "/^
                $textRegex{{$offset}}
                ($textRegex{$multiplier})
                [\s\S]*$
            /ux",
            '$1',
            $text
        );
    }

    public function getLastColor(): ?string
    {
        if (!$this->config ||
            !$this->config->areColorsSupported() ||
            !preg_match('/(?:[\s\S]*)'.$this->getColorCodeRegex('', true).'/', (string) $this, $match) ||
            $match[1] === $this->config->getNoneColor()
        ) {
            return null;
        }

        return $match[1];
    }

    public function wrapColor(): void
    {
        if ($this->getLastColor()) {
            $this->string = $this->string.$this->config->uncolorize();
        }
    }

    public function __toString(): string
    {
        return (string) $this->string;
    }

    public function writeWith(Writer $writer): void
    {
        $writer->write((string) $this, $this->getColor(), $this->getBackground());
    }
}
