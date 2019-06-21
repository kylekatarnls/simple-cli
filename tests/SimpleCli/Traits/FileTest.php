<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\File
 */
class FileTest extends TestCase
{
    /**
     * @covers ::getFile
     */
    public function testGetFile()
    {
        $command = new DemoCli();

        ob_start();
        $command('foobar');
        ob_end_clean();

        static::assertSame('foobar', $command->getFile());
    }
}
