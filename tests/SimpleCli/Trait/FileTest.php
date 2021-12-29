<?php

namespace Tests\SimpleCli\Trait;

use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Trait\File
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
