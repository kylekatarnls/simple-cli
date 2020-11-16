<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Closure;
use InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Table
{
    /** @var string[] */
    public $align = [];

    /** @var string */
    public $fill = ' ';

    /** @var string */
    public $template = '
        !template!
        ┌───┬───┐
        │ 1 │ 2 │
        ├───┼───┤
        │ 3 │ 4 │
        └───┴───┘';

    /** @var iterable */
    protected $source;

    /** @var string|null */
    protected $output;

    public function __construct(iterable $source)
    {
        $this->source = $source;
    }

    public function format(): string
    {
        if (!isset($this->output)) {
            $columnsSizes = [];
            $data = [];

            foreach ($this->source as $key => $row) {
                if (!is_iterable($row)) {
                    $row = [$key, $row];
                }

                $line = [];

                foreach ($row as $cell) {
                    $index = count($line);
                    $align = ($cell instanceof Cell ? $cell->getAlign() : null) ?? $this->align[$index] ?? null;
                    $text = (string) $cell;
                    $columnsSizes[$index] = max(
                        $columnsSizes[$index] ?? 0,
                        mb_strlen(preg_replace('/\033\[[0-9;]+m/', '', $text))
                    );
                    $line[] = [$align, $text];
                }

                $data[] = $line;
            }

            $template = str_replace("\r\n", "\n", $this->template);

            if (preg_match('/\s*\n([ \t]+)!template!\n([\s\S]+)$/', $template, $match)) {
                $template = preg_replace('/^'.$match[1].'/m', '', $match[2]);
            }

            if (!preg_match('/^((?:.*\n)?)(.*)1(.*)2(.*)\n((?:.+\n)?).*3.*4.*\n([\s\S]*)$/', $template, $match)) {
                throw new InvalidArgumentException(
                    "Unable to parse the table template.\n".
                    "It must contain:\n".
                    "- 0, 1 or more header line(s),\n".
                    "- 1 line containing '1' and '2' representing 2 cells,\n".
                    "- 0, 1 or more separation line(s),\n".
                    "- 1 line containing '3' and '4' representing 2 other cells,\n".
                    "- 0, 1 or more footer line(s),\n"
                );
            }

            [, $header, $left, $center, $right, $middle, $footer] = $match;
            $split = $this->getSplitter($left, $center);
            $header = $split($header);
            $middle = $split($middle);
            $footer = $split($footer);

            $this->output = '';

            foreach ($data as $index => $line) {
                $this->addBarToOutput($index ? $middle : $header, $columnsSizes);

                foreach ($columnsSizes as $cellIndex => $size) {
                    [$align, $text] = $line[$cellIndex] ?? [null, ''];
                    $this->output .= ($cellIndex ? $center : $left).$this->pad($text, $size, $align);
                }

                $this->output .= "$right\n";
            }

            $this->addBarToOutput($footer, $columnsSizes);
        }

        return $this->output;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    protected function addBarToOutput(array $bar, array $columnsSizes): void
    {
        foreach ($bar as $lineIndex => $line) {
            if ($lineIndex) {
                $this->output .= "\n";
            }

            foreach ($columnsSizes as $index => $size) {
                $this->output .= $line[$index ? 2 : 1] . str_repeat($line[0], $size);
            }

            $this->output .= $line[3];
        }
    }

    protected function pad(string $text, int $length, ?string $align): string
    {
        return str_pad($text, $length, $this->fill, $this->getStringPadAlign($align));
    }

    /**
     * @param string $left
     * @param string $center
     * @param string $right
     *
     * @return Closure
     * @phpstan-return Closure<string[][]>
     */
    protected function getSplitter(string $left, string $center): Closure
    {
        $leftLength = mb_strlen($left);
        $centerLength = mb_strlen($center);

        return static function (string $template) use ($leftLength, $centerLength) {
            return array_map(static function (string $line) use ($leftLength, $centerLength) {
                return [
                    mb_substr($line, $leftLength, 1),
                    mb_substr($line, 0, $leftLength),
                    mb_substr($line, $leftLength + 1, $centerLength),
                    mb_substr($line, $leftLength + $centerLength + 2),
                ];
            }, explode("\n", $template));
        };
    }

    private function getStringPadAlign(?string $align): int
    {
        return ([
            Cell::ALIGN_LEFT => STR_PAD_RIGHT,
            Cell::ALIGN_CENTER => STR_PAD_BOTH,
            Cell::ALIGN_RIGHT => STR_PAD_LEFT,
        ])[$align] ?? STR_PAD_RIGHT;
    }
}
