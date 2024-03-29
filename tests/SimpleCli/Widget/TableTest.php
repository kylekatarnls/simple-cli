<?php

namespace Tests\SimpleCli\Widget;

use Generator;
use InvalidArgumentException;
use SimpleCli\Widget\Cell;
use SimpleCli\Widget\Table;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Widget\Table
 */
class TableTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::resetOutput
     * @covers ::addRowToOutput
     * @covers ::addToOutput
     * @covers ::parseData
     * @covers ::getCellAlign
     * @covers ::addBarToOutput
     * @covers ::addFooterToOutput
     * @covers ::pad
     * @covers ::fill
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidget(): void
    {
        static::assertOutput(
            implode("\n", [
                '┌────────┬──────────────┐',
                '│ artist │ Nina Simone  │',
                '├────────┼──────────────┤',
                '│ song   │ Feeling Good │',
                '└────────┴──────────────┘',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    'artist' => 'Nina Simone',
                    'song'   => 'Feeling Good',
                ]);

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::resetOutput
     * @covers ::addRowToOutput
     * @covers ::addToOutput
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidgetWithColors(): void
    {
        // Color tag are ignored in space calculation
        // Sol align is kept perfect using colors
        static::assertOutput(
            implode("\n", [
                '┌────────┬──────────────┐',
                "│ artist │ \033[0;34mNina Simone\033[0m  │",
                '├────────┼──────────────┤',
                '│ song   │ Feeling Good │',
                '└────────┴──────────────┘',
            ]),
            static function () {
                $cli = new DemoCli();
                $cli->setEscapeCharacter("\033");
                $table = new Table([
                    'artist' => $cli->colorize('Nina Simone', 'blue'),
                    'song'   => 'Feeling Good',
                ]);

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addRowToOutput
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidgetWithRowIterators(): void
    {
        static::assertOutput(
            implode("\n", [
                '┌────────┬──────────────┐',
                "│ artist │ \033[0;34mNina Simone\033[0m  │",
                '├────────┼──────────────┤',
                '│ song   │ Feeling Good │',
                '└────────┴──────────────┘',
            ]),
            static function () {
                $cli = new DemoCli();
                $cli->setEscapeCharacter("\033");
                $iterator = static function () use ($cli): Generator {
                    yield 'artist' => $cli->colorize('Nina Simone', 'blue');
                    yield 'song'   => 'Feeling Good';
                };
                $table = new Table($iterator());

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addRowToOutput
     * @covers ::getTemplate
     * @covers ::parseData
     * @covers ::getCellAlign
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidgetWithMoreRowsAndColumns(): void
    {
        static::assertOutput(
            implode("\n", [
                '┌──────────────────┬──────────────┬──────┬────────────┐',
                '│ Short            │ A bit longer │    4 │            │',
                '├──────────────────┼──────────────┼──────┼────────────┤',
                '│ Begin            │      OK      │  451 │            │',
                '├──────────────────┼──────────────┼──────┼────────────┤',
                '│ Some other words │    What?     │   62 │ extra info │',
                '├──────────────────┼──────────────┼──────┼────────────┤',
                '│                  │ stick        │ 22.3 │            │',
                '├──────────────────┼──────────────┼──────┼────────────┤',
                '│ Total            │              │  42? │            │',
                '└──────────────────┴──────────────┴──────┴────────────┘',
            ]),
            static function () {
                $cli = new DemoCli();
                $iterator = static function (): Generator {
                    yield '';
                    yield new Cell('stick', Cell::ALIGN_LEFT);
                    yield 22.3;
                };
                $table = new Table([
                    ['Short', 'A bit longer', 4],
                    ['Begin', 'OK', 451],
                    ['Some other words', 'What?', 62, 'extra info'],
                    $iterator(),
                    ['Total', '', '42?'],
                ]);
                $table->align = [Cell::ALIGN_LEFT, Cell::ALIGN_CENTER, Cell::ALIGN_RIGHT];

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addRowToOutput
     * @covers ::addBarToOutput
     * @covers ::addFooterToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidgetCustomTemplate(): void
    {
        static::assertOutput(
            implode("\n", [
                '╔═══════╤═══════╤═══════╗',
                '║>One~~<│>Two~~<│>Three<║',
                '╟───────┼───────┼───────╢',
                '║>Hello<│>World<│>!~~~~<║',
                '╚═══════╧═══════╧═══════╝',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    ['One', 'Two', 'Three'],
                    ['Hello', 'World', '!'],
                ]);
                $table->fill = '~';
                $table->template = '
                    !template!
                    ╔═══╤═══╗
                    ║>1<│>2<║
                    ╟───┼───╢
                    ║>3<│>4<║
                    ╚═══╧═══╝';

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::format
     * @covers ::addRowToOutput
     * @covers ::recordSpan
     * @covers ::shiftSpan
     */
    public function testTableColSpan(): void
    {
        static::assertOutput(
            implode("\n", [
                '╔═══════════╤═══════════╤═══════════╗',
                '║ One       │ Two       │ Three     ║',
                '╟───────────┼───────────┼───────────╢',
                '║    A very long text And so on     ║',
                '╟───────────┼───────────┼───────────╢',
                '║ Hello     │ World     │ !         ║',
                '║ Other     │           │           ║',
                '╚═══════════╧═══════════╧═══════════╝',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    ['One', 'Two', 'Three'],
                    [(new Cell('A very long text And so on', Cell::ALIGN_CENTER))->cols(3)],
                    ['Hello'."\nOther", 'World', '!'],
                ]);
                $table->template = '
                    !template!
                    ╔═══╤═══╗
                    ║ 1 │ 2 ║
                    ╟───┼───╢
                    ║ 3 │ 4 ║
                    ╚═══╧═══╝';

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::getTopPad
     */
    public function testTableVerticalAlign(): void
    {
        static::assertOutput(
            implode("\n", [
                '╔═════╤═════╤═══════╗',
                '║     │ 2   │       ║',
                '║ One │ Two │       ║',
                '║     │ 2   │ Three ║',
                '╚═════╧═════╧═══════╝',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    [
                        new Cell('One', null, Cell::ALIGN_MIDDLE),
                        "2\nTwo\n2",
                        new Cell('Three', null, Cell::ALIGN_BOTTOM),
                    ],
                ]);
                $table->template = '
                    !template!
                    ╔═══╤═══╗
                    ║ 1 │ 2 ║
                    ╟───┼───╢
                    ║ 3 │ 4 ║
                    ╚═══╧═══╝';

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::parseData
     * @covers ::recordSpan
     * @covers ::shiftSpan
     * @covers ::addRowToOutput
     * @covers ::addBarToOutput
     * @covers ::getLeftCellBorder
     */
    public function testRowSpan(): void
    {
        static::assertOutput(
            implode("\n", [
                '┌─────────┬─────────┬───────┬───────┐',
                '│ One     │ Two     │ Three │       │',
                '├─────────┼─────────┼───────┼───────┤',
                '│   Double-height   │ 3     │       │',
                '├─                 ─┼───────┼───────┤',
                '│                   │ Hello │ World │',
                '│                   │ Other │       │',
                '└─────────┴─────────┴───────┴───────┘',
            ]),
            static function () {
                $cli = new DemoCli();

                $table = new Table([
                    ['One', 'Two', 'Three'],
                    [(new Cell('Double-height', Cell::ALIGN_CENTER))->rows(2)->cols(2), 2, 3],
                    ['Hello'."\nOther", (new Cell('World', null, Cell::ALIGN_MIDDLE))],
                ]);

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addRowToOutput
     * @covers ::getTemplate
     * @covers ::parseData
     * @covers ::getCellAlign
     * @covers ::addBarToOutput
     * @covers ::addFooterToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getLeftPad
     */
    public function testTableWidgetMultiLineTemplate(): void
    {
        static::assertOutput(
            implode("\n", [
                '╔═════════╤═══════╤═════════╗',
                ' \        |       |        /║',
                '  ╔═══════╤═══════╤═══════╗-║',
                '  ║ One   │ Two   │ Three ║ ║',
                '  ╟───────┼───────┼───────╢-║',
                '  ╟¤¤¤¤¤¤¤┼¤¤¤¤¤¤¤┼¤¤¤¤¤¤¤╢ ║',
                '  ╟───────┼───────┼───────╢-║',
                '  ║ Hello │ World │ !     ║ ║',
                '  ╟───────┼───────┼───────╢-║',
                '  ╟¤¤¤¤¤¤¤┼¤¤¤¤¤¤¤┼¤¤¤¤¤¤¤╢ ║',
                '  ╟───────┼───────┼───────╢-║',
                '  ║ End   │       │       ║ ║',
                '  ╚═══════╧═══════╧═══════╝ ║',
                ' /        |       |        \║',
                '╚═════════╧═══════╧═════════╝',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    ['One', 'Two', 'Three'],
                    ['Hello', 'World', '!'],
                    ['End', '', ''],
                ]);
                $table->template = '
                    !template!
                    ╔═════╤═════╗
                     \    |    /║
                      ╔═══╤═══╗-║
                      ║ 1 │ 2 ║ ║
                      ╟───┼───╢-║
                      ╟¤¤¤┼¤¤¤╢ ║
                      ╟───┼───╢-║
                      ║ 3 │ 4 ║ ║
                      ╚═══╧═══╝ ║
                     /    |    \║
                    ╚═════╧═════╝';

                $cli->write($table);
            },
        );

        static::assertOutput(
            implode("\n", [
                'One   Two   Three',
                'Hello World !    ',
                'End              ',
            ]),
            static function () {
                $cli = new DemoCli();
                $table = new Table([
                    ['One', 'Two', 'Three'],
                    ['Hello', 'World', '!'],
                    ['End', '', ''],
                ]);
                $table->template = "1 2\n3 4";

                $cli->write($table);
            },
        );
    }

    /**
     * @covers ::getTemplate
     */
    public function testTableWidgetIncorrectTemplate(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(implode("\n", [
            'Unable to parse the table template.',
            'It must contain:',
            '  - 0, 1 or more header line(s),',
            "  - 1 line containing '1' and '2' representing 2 cells,",
            '  - 0, 1 or more separation line(s),',
            "  - 1 line containing '3' and '4' representing 2 other cells,",
            '  - 0, 1 or more footer line(s).',
            'Template given:',
            '1 2',
            '3',
        ]));

        $table = new Table([
            ['One', 'Two', 'Three'],
            ['Hello', 'World', '!'],
            ['End', '', ''],
        ]);
        $table->template = "1 2\n3";

        $table->format();
    }
}
