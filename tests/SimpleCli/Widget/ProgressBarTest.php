<?php

namespace Tests\SimpleCli\Widget;

use SimpleCli\Widget\ProgressBar;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Widget\ProgressBar
 */
class ProgressBarTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::start
     * @covers ::end
     * @covers ::setValue
     * @covers ::refresh
     * @covers ::isInProgress
     * @covers ::getBar
     */
    public function testProgressBarWidget(): void
    {
        static::assertOutput(
            "\n".implode("\r", [
                '/   0% [>                                                  ]',
                '-  30% [===============>                                   ]',
                '\  70% [===================================>               ]',
                '¤ 100% [===================================================]',
                "\n",
            ]),
            function () {
                $bar = new ProgressBar(new DemoCli());
                $bar->start();
                $bar->setValue(0.3);
                $bar->setValue(0.7);
                $bar->setValue(1);
                $bar->end();
            }
        );

        static::assertOutput(
            " ‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗\n".implode("\r", [
                '{/   0,0% (›                    )}',
                '{-  37,0% (»»»»»»»›             )}',
                '{\  74,1% (»»»»»»»»»»»»»»»›     )}',
                '{|  92,6% (»»»»»»»»»»»»»»»»»»»› )}',
                '{¤ 100,0% (»»»»»»»»»»»»»»»»»»»»»)}',
                "\n ‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾",
            ]),
            function () {
                $bar = new ProgressBar(new DemoCli());
                $bar->total = 27;
                $bar->decimals = 1;
                $bar->width = 20;
                $bar->before = '{';
                $bar->after = '}';
                $bar->start = " ‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗‗\n";
                $bar->end = "\n ‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾";
                $bar->bar = '»';
                $bar->cursor = '›';
                $bar->barStart = '(';
                $bar->barEnd = ')';
                $bar->decimalPoint = ',';
                $generator = $bar();
                $generator->send(10);
                $generator->send(20);
                $generator->send(25);
                $generator->send(27);
            }
        );

        static::assertOutput(
            "\n".implode("\r", [
                '/   0% [░░░░░]',
                '-  60% [███░░]',
                '¤ 100% [█████]',
                "\n",
            ]),
            function () {
                $bar = new ProgressBar(new DemoCli());
                $bar->width = 5;
                $bar->bar = '█';
                $bar->cursor = '';
                $bar->emptyBar = '░';
                $generator = $bar();
                $generator->send(0.6);
                $generator->send(1);
            }
        );

        static::assertOutput(
            "\n".implode("\r", [
                '/   0% [>                                                  ]',
                '-  30% [===============>                                   ]',
                '\  30% [===============>                                   ]',
                '|  30% [===============>                                   ]',
                '/  30% [===============>                                   ]',
                '¤ 100% [===================================================]',
                "\n",
            ]),
            function () {
                $bar = new ProgressBar(new DemoCli());
                $bar->start();
                $bar->setValue(0.3);
                $bar->refresh();
                $bar->refresh();
                $bar->refresh();
                $bar->setValue(1);
                $bar->end();
            }
        );
    }
}
