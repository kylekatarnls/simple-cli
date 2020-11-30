<?php

namespace Tests\SimpleCli\Widget;

use Generator;
use InvalidArgumentException;
use SimpleCli\Widget\Cell;
use SimpleCli\Widget\Table;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Widget\Cell
 */
class CellTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::getContent
     */
    public function testCellStringCast(): void
    {
        $cell = new Cell('foobar');

        self::assertSame('foobar', (string) $cell);
    }

    /**
     * @covers ::cols
     * @covers ::getColSpan
     */
    public function testCellColSpan(): void
    {
        $cell = (new Cell('foobar'))->cols(3);

        self::assertSame(3, $cell->getColSpan());
    }

    /**
     * @covers ::rows
     * @covers ::getRowSpan
     */
    public function testCellRowSpan(): void
    {
        $cell = (new Cell('foobar'))->rows(2);

        self::assertSame(2, $cell->getRowSpan());
    }

    /**
     * @covers ::getHorizontalAlign
     */
    public function testHorizontalAlign(): void
    {
        $cell = new Cell('foobar', Cell::ALIGN_CENTER);

        self::assertSame(Cell::ALIGN_CENTER, $cell->getHorizontalAlign());
    }

    /**
     * @covers ::getVerticalAlign
     */
    public function testVerticalAlign(): void
    {
        $cell = new Cell('foobar', null, Cell::ALIGN_MIDDLE);

        self::assertSame(Cell::ALIGN_MIDDLE, $cell->getVerticalAlign());
    }
}
