<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Closure;
use InvalidArgumentException;
use function preg_match;

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

    /** @var iterable<mixed> */
    protected $source;

    /** @var string|null */
    protected $output = null;

    /**
     * @param iterable<mixed> $source
     */
    public function __construct(iterable $source)
    {
        $this->source = $source;
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @return string
     */
    public function format(): string
    {
        if ($this->output === null) {
            [$data, $columnsSizes] = $this->parseData();
            $template = $this->getTemplate();

            /**
             * @var string      $header
             * @var string      $left
             * @var string      $center
             * @var string      $right
             * @var string      $middle
             * @var string|null $footer
             */
            [, $header, $left, $center, $right, $middle, $footer] = array_pad($template, 7, null);
            $split = $this->getSplitter($left, $center);
            /** @var string[][] $header */
            $header = $split($header);
            /** @var string[][] $middle */
            $middle = $split($middle);

            $this->output = '';

            foreach ($data as $index => $row) {
                $this->addBarToOutput($index ? $middle : $header, $columnsSizes);
                $this->addRowToOutput($row, $columnsSizes, $left, $center, $right);
            }

            $this->addFooterToOutput($split, $footer, $columnsSizes);
        }

        return (string) $this->output;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedVariable)
     *
     * @return string[]
     */
    protected function getTemplate(): array
    {
        $template = str_replace("\r\n", "\n", (string) $this->template);

        if (preg_match('/\s*\n([ \t]+)!template!\n([\s\S]+)$/', $template, $match)) {
            $template = preg_replace('/^'.$match[1].'/m', '', $match[2]);
        }

        if (!preg_match('/^((?:.*\n)*)(.*)1(.*)2(.*)\n((?:.+\n)*).*3.*4.*(?:\n([\s\S]*))?$/', $template, $match)) {
            throw new InvalidArgumentException(
                "Unable to parse the table template.\n".
                "It must contain:\n".
                "  - 0, 1 or more header line(s),\n".
                "  - 1 line containing '1' and '2' representing 2 cells,\n".
                "  - 0, 1 or more separation line(s),\n".
                "  - 1 line containing '3' and '4' representing 2 other cells,\n".
                "  - 0, 1 or more footer line(s).\n".
                "Template given:\n$template"
            );
        }

        return $match;
    }

    /**
     * @return array{list<list<array{null|string, string[], int[], int}>>, array<int, int>}
     */
    protected function parseData(): array
    {
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
                $text = explode("\n", (string) $cell);
                $lengths = array_map(static function ($line) {
                    return mb_strlen(preg_replace('/\033\[[0-9;]+m/', '', $line) ?: '');
                }, $text);
                $colSpan = $cell instanceof Cell ? $cell->getColSpan() : 1;
                $line[] = [$align, $text, $lengths, $colSpan];
                $size = ceil(max($lengths) / $colSpan);

                for ($skip = 0; $skip < $colSpan; $skip++) {
                    $columnsSizes[$index + $skip] = (int) max($columnsSizes[$index + $skip] ?? 0, $size);
                }
            }

            $data[] = $line;
        }

        return [$data, $columnsSizes];
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param string[][] $bar
     * @param int[]      $columnsSizes
     */
    protected function addBarToOutput(array $bar, array $columnsSizes): void
    {
        foreach ($bar as $lineIndex => $line) {
            if ($lineIndex) {
                $this->output .= "\n";
            }

            foreach ($columnsSizes as $index => $size) {
                $this->output .= $line[$index ? 2 : 1].str_repeat($line[0], $size);
            }

            $this->output .= $line[3];
        }
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param array  $row          list of cells as [align, text-lines, lines-lengths, colspan]
     * @param int[]  $columnsSizes calculated sizes of each columns
     * @param string $left         left end border
     * @param string $center       border between cells
     * @param string $right        right end border
     *
     * @psalm-param list<array{null|string, string[], int[], int}> $row
     * @psalm-param array<int, int>                                $columnsSizes
     */
    protected function addRowToOutput(
        array $row,
        array $columnsSizes,
        string $left,
        string $center,
        string $right
    ): void {
        $span = 0;
        /** @var int $textHeight */
        $textHeight = max(array_map(static function ($cell) {
            return count($cell[1]);
        }, $row));

        for ($textY = 0; $textY < $textHeight; $textY++) {
            foreach ($columnsSizes as $cellIndex => $size) {
                if ($span > 0) {
                    $span--;

                    continue;
                }

                /**
                 * @var string|null $align
                 * @var string[]    $text
                 * @var int[]       $lengths
                 * @var int         $colSpan
                 */
                [$align, $text, $lengths, $colSpan] = $row[$cellIndex] ?? [null, [], [], 1];
                $colSpan--;

                if ($colSpan > 0) {
                    $span += $colSpan;
                    $size += mb_strlen($center) * $colSpan +
                        array_sum(array_map(function ($nextIndex) use ($columnsSizes) {
                            return $columnsSizes[(int) $nextIndex];
                        }, range($cellIndex + 1, $cellIndex + $colSpan)));
                }

                /** @var int $size */
                $this->output .= ($cellIndex ? $center : $left).
                    $this->pad($text[$textY] ?? '', $lengths[$textY] ?? 0, $size, $align);
            }

            $this->output .= "$right\n";
        }
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param Closure     $split
     * @param string|null $footer
     * @param int[]       $columnsSizes
     *
     * @psalm-param Closure(string): string[][] $split
     */
    protected function addFooterToOutput(Closure $split, ?string $footer, array $columnsSizes): void
    {
        if ($footer === null) {
            $output = $this->output ?? '';

            if (substr($output, -1) === "\n") {
                $this->output = substr($output, 0, -1);
            }

            return;
        }

        $this->addBarToOutput($split($footer), $columnsSizes);
    }

    protected function fill(int $length): string
    {
        return mb_substr(str_repeat($this->fill, (int) ceil($length / mb_strlen($this->fill))), 0, $length);
    }

    protected function pad(string $text, int $currentLength, int $expectedLength, ?string $align): string
    {
        $leftFillLength = (int) max(0, (($expectedLength - $currentLength) * $this->getLeftPad($align)));

        return $this->fill($leftFillLength).
            $text.
            $this->fill($expectedLength - $currentLength - $leftFillLength);
    }

    /**
     * @phan-suppress PhanTypeMismatchDeclaredReturn
     *
     * @param string $left
     * @param string $center
     *
     * @return Closure(string): string[][]
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

    private function getLeftPad(?string $align): float
    {
        return ([
            Cell::ALIGN_LEFT   => 0,
            Cell::ALIGN_CENTER => 0.5,
            Cell::ALIGN_RIGHT  => 1,
        ])[(string) $align] ?? 0;
    }
}
