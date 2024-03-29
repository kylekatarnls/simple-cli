<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Closure;
use InvalidArgumentException;
use function preg_match;
use SimpleCli\Widget\Traits\TableOutput;
use SimpleCli\Widget\Traits\TableSpan;
use Stringable;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Table
{
    use TableOutput;
    use TableSpan;

    /** @var string[] */
    public $align = [];

    /** @var string */
    public $fill = ' ';

    /** @var string|Stringable */
    public $template = '
        !template!
        ┌───┬───┐
        │ 1 │ 2 │
        ├───┼───┤
        │ 3 │ 4 │
        └───┴───┘';

    /** @var bool */
    public $cache = true;

    /** @var iterable<mixed> */
    protected $source;

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
        if ($this->output === null || !$this->cache) {
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
            array_unshift($middle, ['', '', '', '']);

            $this->resetOutput();
            /** @psalm-var array<int, array<int, true>> $spannedCells */
            $spannedCells = [];

            foreach ($data as $index => $row) {
                $this->addBarToOutput($index ? $middle : $header, $columnsSizes, $spannedCells);
                $this->addRowToOutput($spannedCells, $row, $columnsSizes, $left, $center, $right);
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
     * @return array
     *
     * @psalm-return array{
     *     list<list<array{null|string, null|string, non-empty-list<string>, non-empty-list<int>, int, int}>>,
     *     array<int, int>
     * }
     */
    protected function parseData(): array
    {
        $columnsSizes = [];
        $data = [];
        /** @psalm-var array<int, array<int, true>> $spannedCells */
        $spannedCells = [];

        foreach ($this->source as $key => $row) {
            if (!is_iterable($row)) {
                $row = [$key, $row];
            }

            $line = [];
            $startIndex = 0;

            foreach ($row as $cell) {
                $index = count($line);

                while ($spannedCells[0][$index + $startIndex] ?? false) {
                    $columnsSizes[$index + $startIndex] = $columnsSizes[$index + $startIndex] ?? 0;
                    $startIndex++;
                }

                [$horizontalAlign, $verticalAlign] = $this->getCellAlign($index, $cell);
                /** @var non-empty-list<string> $text */
                $text = explode("\n", (string) $cell);
                /** @var non-empty-list<int> $lengths */
                $lengths = array_map(static function ($line) {
                    return mb_strlen(preg_replace('/\033\[[0-9;]+m/', '', $line) ?: '');
                }, $text);
                $colSpan = $cell instanceof Cell ? $cell->getColSpan() : 1;
                $rowSpan = $cell instanceof Cell ? $cell->getRowSpan() : 1;
                $this->recordSpan($spannedCells, $colSpan, $rowSpan);
                $line[] = [$horizontalAlign, $verticalAlign, $text, $lengths, $colSpan, $rowSpan];
                $size = ceil(max($lengths) / $colSpan);

                for ($skip = 0; $skip < $colSpan; $skip++) {
                    $columnIndex = $index + $skip + $startIndex;
                    $columnsSizes[$columnIndex] = (int) max($columnsSizes[$columnIndex] ?? 0, $size);
                }
            }

            $data[] = $line;
            $this->shiftSpan($spannedCells);
        }

        return [$data, $columnsSizes];
    }

    /**
     * @param int         $columnIndex
     * @param string|Cell $cell
     *
     * @return array{string|null, string|null}
     */
    protected function getCellAlign(int $columnIndex, $cell): array
    {
        /** @var array{string|null, string|null} $default */
        $default = array_pad((array) ($this->align[$columnIndex] ?? []), 2, null);

        if (!($cell instanceof Cell)) {
            return $default;
        }

        [$horizontalAlign, $verticalAlign] = $default;

        return [
            $cell->getHorizontalAlign() ?? $horizontalAlign,
            $cell->getVerticalAlign() ?? $verticalAlign,
        ];
    }

    /**
     * @param int                          $cellIndex
     * @param string                       $left
     * @param string                       $center
     * @param array<int, array<int, true>> $spannedCells record of spanned cells for next/from previous rows
     *
     * @return string
     */
    protected function getLeftCellBorder(int $cellIndex, string $left, string $center, array $spannedCells = []): string
    {
        if (!$cellIndex) {
            return $left;
        }

        return ($spannedCells[0][$cellIndex - 1] ?? false) && ($spannedCells[0][$cellIndex] ?? false)
            ? $this->fill(mb_strlen($center))
            : $center;
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param string[][]                   $bar
     * @param int[]                        $columnsSizes
     * @param array<int, array<int, true>> $spannedCells record of spanned cells for next/from previous rows
     */
    protected function addBarToOutput(array $bar, array $columnsSizes, array $spannedCells = []): void
    {
        foreach ($bar as $lineIndex => $line) {
            if ($lineIndex) {
                $this->addToOutput("\n");
            }

            foreach ($columnsSizes as $index => $size) {
                $barEnd = str_repeat($line[0], $size);
                $barString = $line[$index ? 2 : 1].$barEnd;

                if ($spannedCells[0][$index] ?? false) {
                    $barString = $index
                        ? $this->fill(mb_strlen($barString))
                        : $line[1].$this->fill(mb_strlen($barEnd));
                }

                $this->addToOutput($barString);
            }

            $this->addToOutput($line[3]);
        }
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param array<int, array<int, true>> $spannedCells record of spanned cells for next/from previous rows
     * @param array                        $row          list of cells as [align, text-lines, lines-lengths, colspan]
     * @param int[]                        $columnsSizes calculated sizes of each columns
     * @param string                       $left         left end border
     * @param string                       $center       border between cells
     * @param string                       $right        right end border
     *
     * @psalm-param list<array{null|string, null|string, list<string>, list<false|int>, int, int}> $row
     * @psalm-param array<int, int>                                                                $columnsSizes
     */
    protected function addRowToOutput(
        array &$spannedCells,
        array $row,
        array $columnsSizes,
        string $left,
        string $center,
        string $right
    ): void {
        $span = 0;
        /** @var int $textHeight */
        $textHeight = empty($row) ? 0 : max(array_map(static function ($cell) {
            return count($cell[2]);
        }, $row));

        for ($textY = 0; $textY < $textHeight; $textY++) {
            $columnSkip = 0;

            if ($textY) {
                $this->addToOutput("\n");
            }

            foreach ($columnsSizes as $cellIndex => $size) {
                if ($span > 0) {
                    $span--;

                    continue;
                }

                $firstBorder = $this->getLeftCellBorder($cellIndex, $left, $center, $spannedCells);

                if ($spannedCells[0][$cellIndex] ?? false) {
                    $columnSkip++;
                    $this->addToOutput($firstBorder.$this->fill($size));

                    continue;
                }

                /**
                 * @var string|null $horizontalAlign
                 * @var string|null $verticalAlign
                 * @var string[]    $text
                 * @var int[]       $lengths
                 * @var int         $colSpan
                 * @var int         $rowSpan
                 */
                [$horizontalAlign, $verticalAlign, $text, $lengths, $colSpan, $rowSpan] = $row[$cellIndex - $columnSkip]
                    ?? [null, null, [], [], 1, 1];
                $firstLine = (int) (($textHeight - count($text)) * $this->getTopPad($verticalAlign));
                $lineIndex = $textY - $firstLine;
                $this->recordSpan($spannedCells, $colSpan, $rowSpan);

                $colSpan--;

                if ($colSpan > 0) {
                    $span += $colSpan;
                    $size += mb_strlen($center) * $colSpan +
                        array_sum(array_map(function (int $nextIndex) use ($columnsSizes) {
                            return $columnsSizes[$nextIndex];
                        }, range($cellIndex + 1, $cellIndex + $colSpan)));
                }

                /** @var int $size */
                $this->addToOutput(
                    $firstBorder.
                    $this->pad($text[$lineIndex] ?? '', $lengths[$lineIndex] ?? 0, $size, $horizontalAlign)
                );
            }

            $this->addToOutput($right);
        }

        $this->shiftSpan($spannedCells);
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
        if ($footer !== null) {
            $this->addToOutput("\n");
            $this->addBarToOutput($split($footer), $columnsSizes);
        }
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
        return [
            Cell::ALIGN_LEFT   => 0,
            Cell::ALIGN_CENTER => 0.5,
            Cell::ALIGN_RIGHT  => 1,
        ][(string) $align] ?? 0;
    }

    private function getTopPad(?string $align): float
    {
        return [
            Cell::ALIGN_TOP    => 0,
            Cell::ALIGN_MIDDLE => 0.5,
            Cell::ALIGN_BOTTOM => 1,
        ][(string) $align] ?? 0;
    }
}
