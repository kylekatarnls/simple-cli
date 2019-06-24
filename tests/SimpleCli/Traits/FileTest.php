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
        $command->mute();

        $command('foobar');

        static::assertSame('foobar', $command->getFile());
    }
}
