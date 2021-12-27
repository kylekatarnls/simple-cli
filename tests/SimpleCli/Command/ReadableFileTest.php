<?php

namespace Tests\SimpleCli\Command;

use Tests\SimpleCli\DemoApp\FileCli;
use Tests\SimpleCli\TestCase;

class ReadableFileTest extends TestCase
{
    /**
     * @covers \SimpleCli\Attribute\ReadableFile::proceed
     * @covers \SimpleCli\Attribute\WritableFile::proceed
     * @covers \SimpleCli\Traits\Validations::validateValueWith
     * @covers \SimpleCli\Traits\Validations::validateExpectedOptions
     */
    public function testCopy(): void
    {
        static::assertOutput(
            'copy '.__FILE__.' to '.sys_get_temp_dir()."/out\n",
            static function () {
                $command = new FileCli();
                $command('file', 'copy', '-i', __FILE__, '-o', sys_get_temp_dir().'/out');
            },
        );

        static::assertOutput(
            'copy '.__FILE__.' to '.sys_get_temp_dir().DIRECTORY_SEPARATOR."out\n",
            static function () {
                $wd = getcwd();
                chdir(sys_get_temp_dir());
                $command = new FileCli();
                $command('file', 'copy', '-i', __FILE__, '-o', 'out');
                chdir($wd);
            },
        );

        static::assertOutput(
            'copy '.sys_get_temp_dir().DIRECTORY_SEPARATOR.'dup.txt to '.
            sys_get_temp_dir().DIRECTORY_SEPARATOR."out\n",
            static function () {
                copy(__FILE__, sys_get_temp_dir().DIRECTORY_SEPARATOR.'dup.txt');
                $wd = getcwd();
                chdir(sys_get_temp_dir());
                $command = new FileCli();
                $command('file', 'copy', '-i', 'dup.txt', '-o', 'out');
                chdir($wd);
            },
        );

        static::assertOutput(
            '[ESCAPE][0;31mValidation failed for inputFile: '.
            var_export('i-do-not-exist/dup.txt', true).
            ' is not a readable file path.[ESCAPE][0m',
            static function () {
                $wd = getcwd();
                chdir(sys_get_temp_dir());
                $command = new FileCli();
                $command('file', 'copy', '-i', 'i-do-not-exist/dup.txt', '-o', 'out');
                chdir($wd);
            },
        );

        static::assertOutput(
            '[ESCAPE][0;31mValidation failed for outputFile: '.
            var_export('i-do-not-exist/out', true).
            ' is not a writable file path.[ESCAPE][0m',
            static function () {
                $wd = getcwd();
                chdir(sys_get_temp_dir());
                $command = new FileCli();
                $command('file', 'copy', '-i', 'dup.txt', '-o', 'i-do-not-exist/out');
                chdir($wd);
            },
        );
    }
}
