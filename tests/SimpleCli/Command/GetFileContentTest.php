<?php

namespace Tests\SimpleCli\Command;

use Tests\SimpleCli\DemoApp\FileCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Attribute\GetFileContent
 */
class GetFileContentTest extends TestCase
{
    /**
     * @covers ::proceed
     * @covers \SimpleCli\Traits\Validations::validateValueWith
     * @covers \SimpleCli\Traits\Validations::validateExpectedOptions
     */
    public function testGetFileContent(): void
    {
        static::assertOutput(
            file_get_contents(__FILE__),
            static function () {
                $command = new FileCli();

                $command('file', 'read', '--input', __FILE__);
            },
        );

        $path = __FILE__.'/not';
        $var = var_export($path, true);

        static::assertOutput(
            "[ESCAPE][0;31mValidation failed for input: $var is not a readable file path.[ESCAPE][0m",
            static function () use ($path) {
                $command = new FileCli();

                $command('file', 'read', '--input', $path);
            },
        );

        static::assertOutput(
            '[ESCAPE][0;31minput is mandatory.[ESCAPE][0m',
            static function () use ($path) {
                $command = new FileCli();

                $command('file', 'read');
            },
        );
    }

    /**
     * @covers ::proceed
     * @covers \SimpleCli\Traits\Validations::validateValueWith
     * @covers \SimpleCli\Traits\Validations::validateExpectedOptions
     * @covers \SimpleCli\Traits\Parameters::getTypesFromDefinition
     */
    public function testGetFileContentOptionalParameter(): void
    {
        static::assertOutput(
            'empty',
            static function () {
                $command = new FileCli();

                $command('file', 'opt');
            },
        );
    }
}
