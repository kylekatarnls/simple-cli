<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Closure;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use SimpleCli\CliConfig;
use SimpleCli\EnhancedString;
use Stringable;
use function preg_match;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Table implements Stringable, IteratorAggregate
{
    /** @var string[] */
    public $align = [];

    /** @var array<int|null> */
    public $minWidth = [];

    /** @var array<int|null> */
    public $maxWidth = [];

    /** @var array<int|null> */
    public $fixedWidth = [];

    /** @var int */
    public $minHeight = 0;

    /** @var float */
    public $maxHeight = INF;

    /** @var int|null */
    public $fixedHeight = null;

    /** @var string[]|int[]|int|null */
    public $columns = null;

    /** @var string */
    public $ellipsis = '…';

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

    /** @var string */
    public $spanTemplate = [
        '┬' => ['┬', '─', '─'],
        '┼' => ['┬', '┴', '─'],
        '┴' => ['─', '┴', '─'],
        '├' => ['├', '│', '│'],
        '┤' => ['│', '┤', '│'],
    ];

    /** @var bool */
    public $cache = true;

    /** @var int */
    protected $maximumNumberOfColumns = INF;

    /** @var iterable<mixed> */
    protected $source;

    /** @var string[]|null */
    protected $output = null;

    /** @var int */
    protected $outputIndex = 0;

    /** @var int[] */
    protected $columnsSizes = [];

    /** @var bool */
    protected $allWidthFixed = false;

    /** @var int */
    protected $ellipsisLength = 1;

    /** @var int */
    protected $fillLength = 1;

    /** @var int[] */
    protected $minWidthCache = [];

    /** @var float[] */
    protected $maxWidthCache = [];

    /** @var CliConfig */
    protected $config;

    /** @var iterable */
    protected $lines;

    /** @var iterable */
    protected $preLoad = [];

    /** @var array|null */
    protected $previousLine = null;

    /**
     * @param iterable<mixed> $source
     */
    public function __construct(iterable $source, CliConfig $config = null)
    {
        $this->source = $source;
        $this->config = $config ?? new CliConfig();
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @return string
     */
    public function format(): string
    {
        return implode(
            '',
            $this->output === null || !$this->cache
                ? iterator_to_array($this->compile())
                : $this->output
        );
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function getIterator(): iterable
    {
        yield from $this->output === null || !$this->cache
            ? $this->compile()
            : $this->output;
    }

    protected function compile(): Generator
    {
        $this->initConfig();
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
        $this->outputIndex = 0;
        $spannedCells = [];
        $this->lines = $this->parseData();

        foreach ($this->getLines() as $index => $row) {
            $this->addBarToOutput($index ? $middle : $header);

            yield $this->popOutput();

            $this->addRowToOutput($spannedCells, $row, $left, $center, $right);
        }

        $this->addFooterToOutput($split, $footer);

        yield $this->popOutput();
    }

    protected function getLines(): Generator
    {
        foreach ($this->lines as $line) {
            yield $line;
            $this->previousLine = $line;

            while ($line = array_shift($this->preLoad)) {
                yield $line;
                $this->previousLine = $line;
            }
        }
    }

    protected function getNextLine(int $offset = 1): ?array
    {
        if (isset($this->preLoad[$offset])) {
            return $this->preLoad[$offset];
        }

        $this->lines->next();
        $current = null;
        $index = 0;

        while ($this->lines->valid()) {
            $current = $this->lines->current();
            $this->preLoad[] = $current;

            if ($index === $offset) {
                break;
            }

            $this->lines->next();
        }

        return $current;
    }

    protected function calculatedFixedWidth(): ?array
    {
        if (count($this->minWidth) !== count($this->maxWidth)) {
            return null;
        }

        $fixedWidth = [];

        foreach ($this->minWidth as $index => $minWidth) {
            if ($minWidth < $this->maxWidth[$index]) {
                return null;
            }

            $fixedWidth[$index] = $minWidth;
        }

        return $fixedWidth;
    }

    protected function initConfig()
    {
        $this->ellipsisLength = $this->getTextLength($this->ellipsis);
        $this->fillLength = $this->getTextLength($this->fill);

        if ($this->fixedHeight === null && $this->maxHeight <= $this->minHeight) {
            $this->fixedHeight = $this->minHeight;
            $this->maxHeight = $this->minHeight;
        }

        if ($this->fixedWidth === null) {
            $this->fixedWidth = $this->calculatedFixedWidth();
        }

        if (is_int($this->columns)) {
            $this->allWidthFixed = $this->fixedWidth && count($this->fixedWidth) === $this->columns;
            $this->columnsSizes = $this->allWidthFixed ? $this->fixedWidth : $this->minWidth;
            $this->maximumNumberOfColumns = $this->columns;
            $this->columns = null;

            return;
        }

        if ($this->columns === null) {
            $this->columnsSizes = $this->minWidth;

            return;
        }

        if (!is_array($this->columns)) {
            throw new InvalidArgumentException(
                'columns config must be an integer or an array, '.gettype($this->columns).' given.'
            );
        }

        $columns = count($this->columns);
        $this->allWidthFixed = $this->fixedWidth && count($this->fixedWidth) === $columns;
        $this->columnsSizes = $this->allWidthFixed ? $this->fixedWidth : $this->minWidth;
        $this->maximumNumberOfColumns = $columns;
    }

    protected function popOutput(): string
    {
        $output = implode('', array_slice($this->output, $this->outputIndex));
        $this->outputIndex = count($this->output);

        return $output;
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
     * @return iterable
     *
     * @psalm-return iterable<list<array{null|string, string[], int[], int}>>
     */
    protected function parseData(): iterable
    {
        $data = [];

        foreach ($this->source as $key => $row) {
            if (!is_iterable($row)) {
                $row = [$key, $row];
            }

            $line = [];
            $xIndex = 0;

            foreach ($row as $cell) {
                $index = count($line);
                [$horizontalAlign, $verticalAlign] = $this->getCellAlign($index, $cell);
                $text = explode("\n", (string) $cell);

                if (count($text) > $this->maxHeight) {
                    if ($this->maxHeight < INF && $this->containsTextAfterIndex($text, (int) $this->maxHeight)) {
                        $text[$this->maxHeight - 1] .= $this->ellipsis;
                    }

                    $text = array_slice($text, 0, $this->maxHeight);
                }

                $lengths = [];
                $colSpan = $cell instanceof Cell ? $cell->getColSpan() : 1;
                $rowSpan = $cell instanceof Cell ? $cell->getRowSpan() : 1;
                $minWidth = 0;
                $maxWidth = 0;
                $maxWidths = [];
                $currentWidth = 0;

                for ($xSpan = 0; $xSpan < $colSpan; $xSpan++) {
                    $xColumn = $xIndex + $xSpan;
                    $xMinWidth = $this->getMinWidth($xColumn);
                    $xMaxWidth = $this->getMaxWidth($xColumn);
                    $maxWidths[$xColumn] = $xMaxWidth;
                    $this->recordColumnSize($xColumn, $xMinWidth);
                    $minWidth += $xMinWidth;
                    $maxWidth += $xMaxWidth;
                    $currentWidth += $this->columnsSizes[$xColumn] ?? 0;
                }

                foreach ($text as &$textLine) {
                    $length = $this->getTextLength($textLine);

                    if ($length > $maxWidth) {
                        $maxWidth = (int) $maxWidth;
                        $length = $maxWidth;
                        $textLine = $maxWidth > $this->ellipsisLength
                            ? $this->getSubString($textLine, 0, $maxWidth - $this->ellipsisLength).$this->ellipsis
                            : $this->getSubString($this->ellipsis, 0, $maxWidth);
                    }

                    $lengths[] = max($minWidth, $length);
                }

                $line[] = [$horizontalAlign, $verticalAlign, $text, $lengths, $colSpan, $rowSpan];

                $cellWidth = max($lengths);

                if ($this->allWidthFixed) {
                    continue;
                }

                if ($cellWidth > $currentWidth) {
                    $this->fitColumnSize($cellWidth - $currentWidth, $xIndex, $colSpan, $maxWidths);
                }

                $xIndex += $colSpan;
            }

            if (!$this->allWidthFixed) {
                $data[] = $line;

                continue;
            }

            yield $line;
        }

        yield from $data;
    }

    /**
     * @param int   $missing
     * @param int   $index
     * @param int   $colSpan
     * @param int[] $maxWidths
     */
    protected function fitColumnSize(int $missing, int $index, int $colSpan, array $maxWidths): void
    {
        $availability = array_map(function (int $xIndex, $maxWidth) use ($missing) {
            return min($maxWidth, $missing) - $this->columnsSizes[$xIndex];
        }, array_keys($maxWidths), $maxWidths);
        $totalAvailable = array_sum($availability);

        for ($skip = 0; $skip < $colSpan; $skip++) {
            $xIndex = $index + $skip;
            $available = $availability[$xIndex] ?? $missing;
            $add = (int) ceil($available * $missing / ($colSpan - $skip) / $totalAvailable);

            if ($add > 0) {
                $missing -= $add;
                $totalAvailable -= $available;
                $this->recordColumnSize($xIndex, $this->columnsSizes[$xIndex] + $add);
            }
        }
    }

    protected function recordColumnSize(int $index, int $size): void
    {
        if ($size > ($this->columnsSizes[$index] ?? -1)) {
            $this->columnsSizes[$index] = $size;
        }
    }

    protected function getMinWidth(int $index): int
    {
        if (!isset($this->minWidthCache[$index])) {
            $this->minWidthCache[$index] = $this->fixedWidth[$index] ?? $this->minWidth[$index] ?? 0;
        }

        return $this->minWidthCache[$index];
    }

    protected function getMaxWidth(int $index): float
    {
        if (!isset($this->maxWidthCache[$index])) {
            $this->maxWidthCache[$index] = $this->fixedWidth[$index] ?? max(
                $this->minWidth[$index] ?? 0,
                $this->maxWidth[$index] ?? INF
            );
        }

        return $this->maxWidthCache[$index];
    }

    /**
     * Return the number of characters to output with a given string expressed as the space
     * it would take in a terminal (so color codes are ignored and multi-bytes characters are
     * counted as 1).
     *
     * @param string $text
     *
     * @return int
     */
    protected function getTextLength(string $text): int
    {
        return $this->getEnhancedString($text)->getLength();
    }

    /**
     * Get a EnhancedString with the current table config.
     *
     * @param Stringable|string $string
     * @param string|null       $color
     * @param string|null       $background
     *
     * @return EnhancedString
     */
    protected function getEnhancedString($string, ?string $color = null, ?string $background = null): EnhancedString
    {
        return new EnhancedString($string, $color, $background, $this->config);
    }

    /**
     * Return a slice of a string from the given $offset and $length characters-long counting
     * color codes as 0 and multi-bytes characters as 1 so it represent the actual offset and
     * place it would have and take in a terminal.
     *
     * @param string $text
     * @param int    $offset
     * @param int    $length
     *
     * @return string
     */
    protected function getSubString(string $text, int $offset, ?int $length = null): string
    {
        return $this->getEnhancedString($text)->getSubString($text, $offset, $length);
    }

    /**
     * @param int         $columnIndex
     * @param string|Cell $cell
     *
     * @return array{string|null, string|null}
     */
    protected function getCellAlign(int $columnIndex, $cell): array
    {
        $default = array_pad((array) ($this->align[$columnIndex] ?? []), 2, null);

        if (!($cell instanceof Cell)) {
            return $default;
        }

        [$defaultHorizontalAlign, $defaultVerticalAlign] = $default;

        return [
            $cell->getHorizontalAlign() ?? $defaultHorizontalAlign,
            $cell->getVerticalAlign() ?? $defaultVerticalAlign,
        ];
    }

    protected function resetOutput(): void
    {
        $this->output = [];
    }

    protected function addToOutput(string $content): void
    {
        $this->output[] = $content;
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param string[][] $bar
     */
    protected function addBarToOutput(array $bar): void
    {
        foreach ($bar as $lineIndex => $line) {
            if ($lineIndex) {
                $this->addToOutput("\n");
            }

            foreach ($this->columnsSizes as $index => $size) {
                $this->addToOutput($line[$index ? 2 : 1].str_repeat($line[0], $size));
            }

            $this->addToOutput($line[3]);
        }
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param array  &$spannedCells record of spanned cells for next/from previous rows
     * @param array  $row           list of cells as [align, text-lines, lines-lengths, colspan]
     * @param string $left          left end border
     * @param string $center        border between cells
     * @param string $right         right end border
     *
     * @psalm-param list<array{null|string, string[], int[], int}> $row
     */
    protected function addRowToOutput(
        array &$spannedCells,
        array $row,
        string $left,
        string $center,
        string $right
    ): void {
        $span = 0;
        /** @var int $textHeight */
        $textHeight = $this->fixedHeight ?? max(
            $this->minHeight,
            min(
                $this->maxHeight,
                max(array_map(static function ($cell) {
                    return count($cell[2]);
                }, $row))
            )
        );
        $columnSkip = 0;

        for ($textY = 0; $textY < $textHeight; $textY++) {
            if ($textY) {
                $this->addToOutput("\n");
            }

            foreach ($this->columnsSizes as $cellIndex => $size) {
                if ($span > 0) {
                    $span--;

                    continue;
                }

                $firstBorder = $cellIndex ? $center : $left;

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

                for ($rowIndex = 1; $rowIndex < $rowSpan; $rowIndex++) {
                    if (!isset($spannedCells[$rowIndex])) {
                        $spannedCells[$rowIndex] = [];
                    }

                    for ($colIndex = 0; $colIndex < $colSpan; $colIndex++) {
                        $spannedCells[$rowIndex][$colIndex] = true;
                    }
                }

                $colSpan--;

                if ($colSpan > 0) {
                    $span += $colSpan;
                    $size += $this->getTextLength($center) * $colSpan +
                        array_sum(array_map(function ($nextIndex) {
                            return $this->columnsSizes[(int) $nextIndex];
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

        $shiftedSpannedCells = [];

        foreach ($spannedCells as $index => $row) {
            if (!$index) {
                continue;
            }

            $shiftedSpannedCells[$index - 1] = $row;
        }

        $spannedCells = $shiftedSpannedCells;
    }

    /**
     * @param string[] $text
     * @param int      $lineIndex
     *
     * @return bool
     */
    protected function containsTextAfterIndex(array $text, int $lineIndex): bool
    {
        $count = count($text);

        for ($index = $lineIndex; $index < $count; $index++) {
            if ($this->getTextLength($text[$index] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     *
     * @param Closure     $split
     * @param string|null $footer
     *
     * @psalm-param Closure(string): string[][] $split
     */
    protected function addFooterToOutput(Closure $split, ?string $footer): void
    {
        if ($footer !== null) {
            $this->addToOutput("\n");
            $this->addBarToOutput($split($footer));
        }
    }

    protected function fill(int $length): string
    {
        if ($length <= 0) {
            return '';
        }

        return $this->getSubString(
            str_repeat($this->fill, (int) ceil($length / $this->getTextLength($this->fill))),
            0,
            $length
        );
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
        $leftLength = $this->getTextLength($left);
        $centerLength = $this->getTextLength($center);

        return function (string $template) use ($leftLength, $centerLength) {
            return array_map(function (string $line) use ($leftLength, $centerLength) {
                return [
                    $this->getSubString($line, $leftLength, 1),
                    $this->getSubString($line, 0, $leftLength),
                    $this->getSubString($line, $leftLength + 1, $centerLength),
                    $this->getSubString($line, $leftLength + $centerLength + 2),
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

    private function getTopPad(?string $align): float
    {
        return ([
            Cell::ALIGN_TOP    => 0,
            Cell::ALIGN_MIDDLE => 0.5,
            Cell::ALIGN_BOTTOM => 1,
        ])[(string) $align] ?? 0;
    }
}
