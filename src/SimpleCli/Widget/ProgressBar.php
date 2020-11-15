<?php

declare(strict_types=1);

namespace SimpleCli\Widget;

use Generator;
use SimpleCli\SimpleCli;

class ProgressBar
{
    public $total = 1;

    public $decimals = 0;

    public $width = 50;

    public $before = '';

    public $after = '';

    public $start = "\n";

    public $end = "\n";

    public $rewind = "\r";

    public $bar = '=';

    public $barStart = '[';

    public $barEnd = ']';

    public $cursor = '>';

    public $decimalPoint = '.';

    public $thousandsSeparator = '.';

    /** @var SimpleCli */
    protected $cli;

    protected $value = 0;

    protected $step = -1;

    public function __construct(SimpleCli $cli)
    {
        $this->cli = $cli;
    }

    public function start(float $initialValue = 0): void
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
        $this->cli->write($this->getCurrentBar());
    }

    public function isInProgress(): bool
    {
        return $this->value < $this->total;
    }

    public function __invoke(): Generator
    {
        $this->start();

        while ($this->isInProgress()) {
            $this->setValue(yield);
        }

        $this->end();
    }

    protected function getCurrentBar(): string
    {
        $bar = (int) round(($this->width * $this->value) / $this->total);
        $finished = ((float) $this->value === (float) $this->total);
        $length = 3;

        if ($this->decimals > 0) {
            $length += mb_strlen($this->decimalPoint) + $this->decimals;
        }

        return sprintf(
            "%s%s %s%% %s%s%s%s%s%s%s",
            $this->before,
            $finished ? '¤' : ['/', '-', '\\', '|'][$this->step % 4],
            str_pad(number_format(
                ($this->value * 100) / $this->total,
                $this->decimals,
                $this->decimalPoint,
                $this->thousandsSeparator
            ), $length, ' ', STR_PAD_LEFT),
            $this->barStart,
            str_repeat($this->bar, $bar),
            $finished ? $this->bar : $this->cursor,
            str_repeat(' ', $this->width - $bar),
            $this->barEnd,
            $this->after,
            $this->rewind
        );
    }
}