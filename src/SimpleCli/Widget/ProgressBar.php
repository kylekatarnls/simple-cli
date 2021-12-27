<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Generator;
use SimpleCli\SimpleCli;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProgressBar
{
    public int|float $total = 1;
    public int $decimals = 0;
    public int $width = 50;
    public string $before = '';
    public string $after = '';
    public string $start = "\n";
    public string $end = "\n";
    public string $rewind = "\r";
    public string $bar = '=';
    public string $emptyBar = ' ';
    public string $barStart = '[';
    public string $barEnd = ']';
    public string $cursor = '>';
    public string $decimalPoint = '.';
    public string $thousandsSeparator = '.';

    protected SimpleCli $cli;
    protected int|float $value = 0;
    protected int $step = -1;

    public function __construct(SimpleCli $cli)
    {
        $this->cli = $cli;
    }

    public function start(float $initialValue = 0.0): void
    {
        $this->step = -1;
        $this->cli->write(...((array) $this->start));
        $this->setValue($initialValue);
    }

    public function end(): void
    {
        $this->cli->write(...((array) $this->end));
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->step++;
        $this->cli->write($this->getBar());
    }

    public function isInProgress(): bool
    {
        return $this->value < $this->total;
    }

    /**
     * @return Generator<int|float|null>
     */
    public function __invoke(): Generator
    {
        $this->start();

        while ($this->isInProgress()) {
            $this->setValue((float) yield);
        }

        $this->end();
    }

    protected function getBar(): string
    {
        $bar = (int) round(($this->width * $this->value) / $this->total);
        $finished = ((float) $this->value === (float) $this->total);
        $length = 3;

        if ($this->decimals > 0) {
            $length += mb_strlen($this->decimalPoint) + $this->decimals;
        }

        /**
         * @psalm-suppress InvalidArrayOffset $this->step >= 0 at this step
         */
        return sprintf(
            '%s%s %s%% %s%s%s%s%s%s%s',
            $this->before,
            $finished ? '¤' : ['/', '-', '\\', '|'][$this->step % 4],
            str_pad(number_format(
                ($this->value * 100) / $this->total,
                $this->decimals,
                $this->decimalPoint,
                $this->thousandsSeparator,
            ), $length, ' ', STR_PAD_LEFT),
            $this->barStart,
            str_repeat($this->bar, $bar),
            $finished && mb_strlen($this->cursor) ? $this->bar : $this->cursor,
            str_repeat($this->emptyBar, $this->width - $bar),
            $this->barEnd,
            $this->after,
            $this->rewind,
        );
    }
}
