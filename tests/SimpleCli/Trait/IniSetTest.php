<?php

namespace Tests\SimpleCli\Trait;

use SimpleCli\Trait\IniSet;
use SimpleCli\Writer;

/**
 * @coversDefaultClass \SimpleCli\Trait\IniSet
 */
class IniSetTest extends TraitsTestCase
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @covers ::iniSet
     * @covers ::formatValues
     */
    public function testIniSet(): void
    {
        $iniRawValue = ini_get('phar.readonly');
        $iniValue = (bool) (int) $iniRawValue;
        $commands = [];
        $commander = new class() {
            use IniSet;
        };
        $commander->passthruFunction = static function ($command) use (&$commands) {
            $commands[] = $command;
        };
        $commander->iniSet('phar.readonly', $iniValue);
        $commander->iniSet('phar.readonly', (string) $iniRawValue);

        static::assertSame([], $commands);

        $commander->iniSet('phar.readonly', (int) $iniRawValue);

        static::assertSame($iniValue ? [] : [
            PHP_BINARY.' -d phar.readonly=0 '.
            escapeshellarg(get_included_files()[0]).' '.
            implode(' ', array_map('escapeshellarg', [
                ...array_slice($GLOBALS['argv'], 1),
                '--simple-cli-skip-ini-fix',
            ])),
        ], $commands);

        $commands = [];

        $commander->iniSet('phar.readonly', !$iniValue);

        static::assertSame([
            PHP_BINARY.' -d phar.readonly='.($iniValue ? 'Off' : 'On').' '.
            escapeshellarg(get_included_files()[0]).' '.
            implode(' ', array_map('escapeshellarg', [
                ...array_slice($GLOBALS['argv'], 1),
                '--simple-cli-skip-ini-fix',
            ])),
        ], $commands);

        $commander->iniSet('phar.readonly', $iniValue ? 'On' : 'Off');
        $commands = [];
        $commander->iniSet('phar.readonly', $iniValue ? 'Off' : 'On');

        static::assertSame([
            PHP_BINARY.' -d phar.readonly='.($iniValue ? 'Off' : 'On').' '.
            escapeshellarg(get_included_files()[0]).' '.
            implode(' ', array_map('escapeshellarg', [
                ...array_slice($GLOBALS['argv'], 1),
                '--simple-cli-skip-ini-fix',
            ])),
        ], $commands);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @covers ::iniSet
     * @covers ::formatValues
     */
    public function testIniSetWithWriter(): void
    {
        $iniRawValue = ini_get('phar.readonly');
        $iniValue = (bool) (int) $iniRawValue;
        $commands = [];
        $commander = new class() implements Writer {
            use IniSet;

            public array $output = [];

            public function write(string $text = '', string $color = null, string $background = null): void
            {
                $this->output[] = [$text, $color, $background];
            }
        };
        $commander->passthruFunction = static function ($command) use (&$commands) {
            $commands[] = $command;
        };
        $commander->iniSet('phar.readonly', $iniValue);

        static::assertSame([], $commands);

        $commander->iniSet('phar.readonly', !$iniValue);

        static::assertSame([
            PHP_BINARY.' -d phar.readonly='.($iniValue ? 'Off' : 'On').' '.
            escapeshellarg(get_included_files()[0]).' '.
            implode(' ', array_map('escapeshellarg', [
                ...array_slice($GLOBALS['argv'], 1),
                '--simple-cli-skip-ini-fix',
            ])),
        ], $commands);
        static::assertSame([], $commander->output);

        $commander->iniSet('phar.readonly', $iniValue);
        $commands = [];
        $argv = $GLOBALS['argv'];
        $GLOBALS['argv'][] = '--simple-cli-skip-ini-fix';
        $commander->iniSet('phar.readonly', !$iniValue);
        $GLOBALS['argv'] = $argv;

        static::assertSame([], $commands);
        static::assertSame([
            [
                'phar.readonly is '.$iniRawValue.', set phar.readonly='.($iniValue ? 'Off' : 'On').' in '.
                (php_ini_loaded_file() ?: 'php.ini')." and retry.\n",
                'red',
                null,
            ],
        ], $commander->output);
    }
}
