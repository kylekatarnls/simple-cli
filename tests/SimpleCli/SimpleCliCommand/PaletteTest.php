<?php

namespace Tests\SimpleCli\SimpleCliCommand;

use SimpleCli\SimpleCliCommand\Palette;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\SimpleCliCommand\Palette
 */
class PaletteTest extends TestCase
{
    /**
     * @covers \SimpleCli\Trait\Output::getColors
     * @covers \SimpleCli\Trait\Output::getBackgrounds
     * @covers ::run
     */
    public function testRun(): void
    {
        static::assertOutput(
            implode("\n", [
                'Colors:',
                'black             0;30      [ESCAPE][0;30mHello world!',
                '[ESCAPE][0mdark_gray         1;30      [ESCAPE][1;30mHello world!',
                '[ESCAPE][0mblue              0;34      [ESCAPE][0;34mHello world!',
                '[ESCAPE][0mlight_blue        1;34      [ESCAPE][1;34mHello world!',
                '[ESCAPE][0mgreen             0;32      [ESCAPE][0;32mHello world!',
                '[ESCAPE][0mlight_green       1;32      [ESCAPE][1;32mHello world!',
                '[ESCAPE][0mcyan              0;36      [ESCAPE][0;36mHello world!',
                '[ESCAPE][0mlight_cyan        1;36      [ESCAPE][1;36mHello world!',
                '[ESCAPE][0mred               0;31      [ESCAPE][0;31mHello world!',
                '[ESCAPE][0mlight_red         1;31      [ESCAPE][1;31mHello world!',
                '[ESCAPE][0mpurple            0;35      [ESCAPE][0;35mHello world!',
                '[ESCAPE][0mlight_purple      1;35      [ESCAPE][1;35mHello world!',
                '[ESCAPE][0mbrown             0;33      [ESCAPE][0;33mHello world!',
                '[ESCAPE][0myellow            1;33      [ESCAPE][1;33mHello world!',
                '[ESCAPE][0mlight_gray        0;37      [ESCAPE][0;37mHello world!',
                '[ESCAPE][0mwhite             1;37      [ESCAPE][1;37mHello world!',
                '[ESCAPE][0m',
                'Backgrounds:',
                'black             40        [ESCAPE][40mHello world![ESCAPE][0m',
                'dark_gray         48;5;59   [ESCAPE][48;5;59mHello world![ESCAPE][0m',
                'blue              44        [ESCAPE][44mHello world![ESCAPE][0m',
                'light_blue        48;5;63   [ESCAPE][48;5;63mHello world![ESCAPE][0m',
                'green             42        [ESCAPE][42mHello world![ESCAPE][0m',
                'light_green       48;5;40   [ESCAPE][48;5;40mHello world![ESCAPE][0m',
                'cyan              46        [ESCAPE][46mHello world![ESCAPE][0m',
                'light_cyan        48;5;87   [ESCAPE][48;5;87mHello world![ESCAPE][0m',
                'red               41        [ESCAPE][41mHello world![ESCAPE][0m',
                'light_red         48;5;168  [ESCAPE][48;5;168mHello world![ESCAPE][0m',
                'purple            45        [ESCAPE][45mHello world![ESCAPE][0m',
                'light_purple      48;5;164  [ESCAPE][48;5;164mHello world![ESCAPE][0m',
                'brown             43        [ESCAPE][43mHello world![ESCAPE][0m',
                'yellow            48;5;108  [ESCAPE][48;5;108mHello world![ESCAPE][0m',
                'light_gray        47        [ESCAPE][47mHello world![ESCAPE][0m',
                'white             48;5;255  [ESCAPE][48;5;255mHello world![ESCAPE][0m',
                '',
            ]),
            static function () {
                $command = new class() extends DemoCli {
                    /** @return array<class-string> */
                    public function getCommands(): array
                    {
                        return [Palette::class];
                    }
                };

                $command('file', 'palette');
            },
        );
    }
}
