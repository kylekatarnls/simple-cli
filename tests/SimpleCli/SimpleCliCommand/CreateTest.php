<?php

namespace Tests\SimpleCli\SimpleCliCommand;

use Symfony\Component\Filesystem\Filesystem;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\SimpleCliCommand\Create
 */
class CreateTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $currentDirectory;

    protected function setUp()
    {
        parent::setUp();

        $this->fs = new Filesystem();
        $this->currentDirectory = sys_get_temp_dir().'/simple-cli-create-'.mt_rand(0, 9999999);
        $this->fs->mkdir($this->currentDirectory);
        chdir($this->currentDirectory);
    }

    protected function tearDown()
    {
        $this->fs->remove($this->currentDirectory);

        parent::tearDown();
    }

    /**
     * @covers ::copyBinTemplate
     * @covers ::extractName
     * @covers ::run
     */
    public function testCopyBinTemplate()
    {
        static::assertOutput("[ESCAPE][0;36m1 program created.\n[ESCAPE][0m", function () {
            $command = new DemoCli();

            $command('file', 'create', 'Tests\\SimpleCli\\DemoApp\\DemoCli');
        });

        static::assertFileContentEquals("#!/usr/bin/env php
<?php

\$dir = __DIR__.'/..';

if (!file_exists(\$dir.'/autoload.php')) {
    \$dir = __DIR__.'/../vendor';
}

if (!file_exists(\$dir.'/autoload.php')) {
    \$dir = __DIR__.'/../../..';
}

if (!file_exists(\$dir.'/autoload.php')) {
    echo 'Autoload not found.';
    exit(1);
}

include \$dir.'/autoload.php';

exit((new \Tests\SimpleCli\DemoApp\DemoCli())(...\$argv) ? 0 : 1);
", 'bin/demo-cli');
        static::assertFileContentEquals('@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/demo-cli
php "%BIN_TARGET%" %*
', 'bin/demo-cli.bat');

        unlink('bin/demo-cli');
        unlink('bin/demo-cli.bat');

        static::assertOutput('[ESCAPE][1;36mCreating program for Tests\SimpleCli\DemoApp\DemoCli
[ESCAPE][0mCreating bin/demo-cli
Creating bin/demo-cli.bat
[ESCAPE][0;36m1 program created.
[ESCAPE][0m', function () {
            $command = new DemoCli();

            $command('file', 'create', 'Tests\\SimpleCli\\DemoApp\\DemoCli', '--verbose');
        });

        static::assertFileContentEquals("#!/usr/bin/env php
<?php

\$dir = __DIR__.'/..';

if (!file_exists(\$dir.'/autoload.php')) {
    \$dir = __DIR__.'/../vendor';
}

if (!file_exists(\$dir.'/autoload.php')) {
    \$dir = __DIR__.'/../../..';
}

if (!file_exists(\$dir.'/autoload.php')) {
    echo 'Autoload not found.';
    exit(1);
}

include \$dir.'/autoload.php';

exit((new \Tests\SimpleCli\DemoApp\DemoCli())(...\$argv) ? 0 : 1);
", 'bin/demo-cli');
        static::assertFileContentEquals('@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/demo-cli
php "%BIN_TARGET%" %*
', 'bin/demo-cli.bat');
    }

    /**
     * @covers ::run
     * @covers ::error
     */
    public function testRun()
    {
        static::assertOutput('[ESCAPE][0;31mfoobar needs to implementSimpleCli\SimpleCli
[ESCAPE][0m[ESCAPE][0;36m0 programs created.
[ESCAPE][0m', function () {
            $command = new DemoCli();

            $command('file', 'create', 'foobar');
        });

        $this->fs->remove('bin');
        touch('bin');

        static::assertOutput('[ESCAPE][0;31mUnable to create the bin directory
[ESCAPE][0m', function () {
            $command = new DemoCli();

            $command('file', 'create', 'foobar');
        });
    }
}
