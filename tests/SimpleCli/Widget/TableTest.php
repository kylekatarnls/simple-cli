<?php

namespace Tests\SimpleCli\Widget;

use Generator;
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
     * @covers ::addBarToOutput
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
}
