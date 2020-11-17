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
    /** @var int|float */
    public $total = 1;

    /** @var int */
    public $decimals = 0;

    /** @var int */
    public $width = 50;

    /** @var string */
    public $before = '';

    /** @var string */
    public $after = '';

    /** @var string */
    public $start = "\n";

    /** @var string */
    public $end = "\n";

    /** @var string */
    public $rewind = "\r";

    /** @var string */
    public $bar = '=';

    /** @var string */
    public $emptyBar = ' ';

    /** @var string */
    public $barStart = '[';

    /** @var string */
    public $barEnd = ']';

    /** @var string */
    public $cursor = '>';

    /** @var string */
    public $decimalPoint = '.';

    /** @var string */
    public $thousandsSeparator = '.';

    /** @var SimpleCli */
    protected $cli;

    /** @var int|float */
    protected $value = 0;

    /** @var int */
    protected $step = -1;

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
            $this->setValue(yield);
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

        return sprintf(
            '%s%s %s%% %s%s%s%s%s%s%s',
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
            $finished && mb_strlen($this->cursor) ? $this->bar : $this->cursor,
            str_repeat($this->emptyBar, $this->width - $bar),
            $this->barEnd,
            $this->after,
            $this->rewind
        );
    }
}
