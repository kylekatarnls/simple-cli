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
     * @covers ::parseData
     * @covers ::addBarToOutput
     * @covers ::addFooter
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
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
            function () {
                $cli = new DemoCli();
                $table = new Table([
                    'artist' => 'Nina Simone',
                    'song'   => 'Feeling Good',
                ]);

                $cli->write($table);
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
     */
    public function testTableWidgetWithColors(): void
    {
        // Color tag are ignored in space calculation
        // Sol align is kept perfect using colors
        static::assertOutput(
            implode("\n", [
                '┌────────┬──────────────┐',
                "│ artist │ \033[0;34mNina Simone\033[0m │",
                '├────────┼──────────────┤',
                '│ song   │ Feeling Good │',
                '└────────┴──────────────┘',
            ]),
            function () {
                $cli = new DemoCli();
                $cli->setEscapeCharacter("\033");
                $table = new Table([
                    'artist' => $cli->colorize('Nina Simone', 'blue'),
                    'song'   => 'Feeling Good',
                ]);

                $cli->write($table);
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
     */
    public function testTableWidgetWithRowIterators(): void
    {
        static::assertOutput(
            implode("\n", [
                '┌────────┬──────────────┐',
                "│ artist │ \033[0;34mNina Simone\033[0m │",
                '├────────┼──────────────┤',
                '│ song   │ Feeling Good │',
                '└────────┴──────────────┘',
            ]),
            function () {
                $cli = new DemoCli();
                $cli->setEscapeCharacter("\033");
                $iterator = static function () use ($cli): Generator {
                    yield 'artist' => $cli->colorize('Nina Simone', 'blue');
                    yield 'song'   => 'Feeling Good';
                };
                $table = new Table($iterator());

                $cli->write($table);
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::getTemplate
     * @covers ::parseData
     * @covers ::addBarToOutput
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
     * @covers \SimpleCli\Widget\Cell::__construct
     * @covers \SimpleCli\Widget\Cell::__toString
     * @covers \SimpleCli\Widget\Cell::getContent
     * @covers \SimpleCli\Widget\Cell::getAlign
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
            function () {
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
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::addBarToOutput
     * @covers ::addFooter
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
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
            function () {
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
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::format
     * @covers ::getTemplate
     * @covers ::parseData
     * @covers ::addBarToOutput
     * @covers ::addFooter
     * @covers ::pad
     * @covers ::getSplitter
     * @covers ::getStringPadAlign
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
            function () {
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
            }
        );

        static::assertOutput(
            implode("\n", [
                'One   Two   Three',
                'Hello World !    ',
                'End              ',
            ]),
            function () {
                $cli = new DemoCli();
                $table = new Table([
                    ['One', 'Two', 'Three'],
                    ['Hello', 'World', '!'],
                    ['End', '', ''],
                ]);
                $table->template = "1 2\n3 4";

                $cli->write($table);
            }
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
