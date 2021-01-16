<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use SimpleCli\Background;
use SimpleCli\Color;

trait Output
{
    /** @var string */
    protected $lastText = '';

    /**
     * Return $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     *
     * @return string
     */
    public function colorize(string $text = '', ?string $color = null, ?string $background = null): string
    {
        if (!$this->colorsSupported || (!$color && !$background)) {
            return $text;
        }

        $color = $color ? $this->getColorCode($color) : '';
        $background = $background ? $this->getColorCode($background, $this->backgrounds) : '';

        return $color.$background.$text.$this->uncolorize();
    }

    /**
     * Rewind CLI cursor $length characters behind, if $length is omitted, use the last written string length.
     *
     * @param int|null $length
     */
    public function rewind(?int $length = null): void
    {
        if ($this->isMuted()) {
            return;
        }

        if ($length === null) {
            $length = strlen($this->lastText);
        }

        echo sprintf($this->rewindCodePattern, $this->escapeCharacter, $length);
    }

    /**
     * Output $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function write(string $text = '', ?string $color = null, ?string $background = null): void
    {
        if ($this->isMuted()) {
            return;
        }

        $this->lastText = $text;

        if ($color) {
            $text = $this->colorize($text, $color, $background);
        }

        echo $text;
    }

    /**
     * Output $text with given color and background color and add a new line.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function writeLine(string $text = '', ?string $color = null, ?string $background = null): void
    {
        $this->write("$text\n", $color, $background);
    }

    /**
     * Write each item iterated from the given $chunks.
     *
     * Use `yield 'Text'` to output text
     * Use `yield 'Text' => 'red'` to output text in color
     * Use `yield 'Text' => ['red', 'blue']` to output text in color and a background
     * Use `yield 'Text' => [null, 'blue']` to output text with a background
     *
     * @param iterable $chunks
     */
    public function writeChunks(iterable $chunks): void
    {
        foreach ($this->getOutputChunks($chunks) as $line => $colors) {
            $this->write($line, ...$colors);
        }
    }

    /**
     * Write each item iterated from the given $chunks and a new line at the end.
     *
     * Use `yield 'Text'` to output a line with "Text"
     * Use `yield 'Text' => 'red'` to output a line in color
     * Use `yield 'Text' => ['red', 'blue']` to output a line in color and a background
     * Use `yield 'Text' => [null, 'blue']` to output a line with a background
     *
     * @param iterable $chunks
     */
    public function writeLines(iterable $chunks): void
    {
        foreach ($this->getOutputChunks($chunks) as $line => $colors) {
            $this->writeLine($line, ...$colors);
        }
    }

    /**
     * Replace last written line with $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function rewrite(string $text = '', ?string $color = null, ?string $background = null): void
    {
        $this->rewind();
        $this->write($text, $color, $background);
    }

    /**
     * Replace last written line with $text with given color and background color and re-add the new line.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function rewriteLine(string $text = '', ?string $color = null, ?string $background = null): void
    {
        $this->write("\r$text", $color, $background);
    }

    /**
     * Standardize the input iterable stream to output a "text" => ["color", "background"] iteration.
     *
     * @param iterable<int|string, string|string[]> $chunks
     *
     * @return iterable<string, string[]>
     */
    protected function getOutputChunks(iterable $chunks): iterable
    {
        foreach ($chunks as $line => $color) {
            if (is_int($line)) {
                $line = is_array($color) ? array_unshift($color) : (string) $color;

                if (($color instanceof Color) || ($color instanceof Background)) {
                    $color = [
                        $color instanceof Color ? $color->getColor() : null,
                        $color instanceof Background ? $color->getBackground() : null,
                    ];
                }

                if (!is_array($color)) {
                    $color = [];
                }
            }

            yield (string) $line => (array) $color;
        }
    }
}
