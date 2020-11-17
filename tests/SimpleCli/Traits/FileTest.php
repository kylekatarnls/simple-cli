<?php

namespace Tests\SimpleCli\Traits;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\File
 */
class FileTest extends TraitsTestCase
{
    /**
     * @covers ::getFile
     */
    public function testGetFile(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('foobar');

        static::assertSame('foobar', $command->getFile());
    }
}
