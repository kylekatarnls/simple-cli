<?php

namespace Tests\SimpleCli\Command;

use SimpleCli\SimpleCliCommand;
use SimpleCli\SimpleCliCommand\BuildPhar;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\SimpleCliCommand\BuildPhar
 */
class BuildPharTest extends TestCase
{
    /**
     * @covers ::run
     * @covers ::initialize
     * @covers ::buildPharFiles
     * @covers ::buildPhar
     * @covers ::setSubStep
     * @covers ::getPaths
     * @covers ::getFiles
     * @covers ::getClassNames
     * @covers ::getVersionConstantDeclaration
     * @covers ::getClassNamesFromFile
     * @covers \SimpleCli\SimpleCliCommand\Traits\ValidateProgram::validateProgram
     */
    public function testBuildPhar(): void
    {
        if ((int) ini_get('phar.readonly')) {
            $this->markTestSkipped('phar.readonly needs to be Off for this test.');
        }

        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'simple-cli.phar';
        $workFile = getcwd().DIRECTORY_SEPARATOR.'simple-cli.phar';
        $template = sys_get_temp_dir().DIRECTORY_SEPARATOR.'template.php';
        file_put_contents($template, '<?php echo "Hello";');
        touch($workFile);
        touch("$workFile.gz");

        if (file_exists($file)) {
            unlink($file);
        }

        $className = SimpleCliCommand::class;
        $content = strtr((string) static::getActionOutput(static function () use ($file, $template) {
            $cli = new SimpleCliCommand();
            $cli('simple-cli', 'build-phar', '--output-file', $file, '--no-vendor', '--main-template-file', $template);
        }), ["\033" => '[ESCAPE]', "\r" => '']);

        static::assertFileExists($file);

        if (file_exists($file)) {
            unlink($file);
        }

        static::assertFileDoesNotExist($workFile);
        static::assertFileDoesNotExist("$workFile.gz");

        static::assertStringEndsWith(
            "¤ 100% [===================================================]\n".
            "[ESCAPE][0;36m1 program built.\n[ESCAPE][0m",
            $content,
        );
        static::assertStringNotContainsString("[ESCAPE][1;36mBuilding program for \\$className\n[ESCAPE][0m", $content);

        $content = strtr((string) static::getActionOutput(static function () use ($file, $template) {
            $cli = new SimpleCliCommand();
            $cli(
                'simple-cli',
                'build-phar',
                '--output-file',
                $file,
                '--verbose',
                '--no-vendor',
                '--main-template-file',
                $template,
            );
        }), ["\033" => '[ESCAPE]', "\r" => '']);

        static::assertFileExists($file);

        if (file_exists($file)) {
            unlink($file);
        }

        static::assertStringEndsWith(
            "¤ 100% [===================================================]\n".
            "[ESCAPE][0;36m1 program built.\n[ESCAPE][0m",
            strtr($content, ["\033" => '[ESCAPE]', "\r" => '']),
        );
        static::assertStringContainsString("[ESCAPE][1;36mBuilding program for \\$className\n[ESCAPE][0m", $content);
    }

    /**
     * @covers ::run
     * @covers ::initialize
     * @covers ::buildPharFiles
     * @covers ::buildPhar
     * @covers ::setSubStep
     * @covers ::getPaths
     * @covers ::getFiles
     * @covers ::getClassNames
     * @covers ::getVersionConstantDeclaration
     * @covers ::getClassNamesFromFile
     * @covers \SimpleCli\SimpleCliCommand\Traits\ValidateProgram::validateProgram
     */
    public function testBuildPharErrors(): void
    {
        if ((int) ini_get('phar.readonly')) {
            $this->markTestSkipped('phar.readonly needs to be Off for this test.');
        }

        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'simple-cli.phar';
        $template = sys_get_temp_dir().DIRECTORY_SEPARATOR.'template.php';

        $content = strtr((string) static::getActionOutput(static function () use ($file, $template) {
            $cli = new SimpleCliCommand();
            $cli(
                'simple-cli',
                'build-phar',
                '--output-file',
                $file,
                '--no-vendor',
                '--main-template-file',
                $template,
                'I\\Do\\Not\\Exist',
            );
        }), ["\033" => '[ESCAPE]', "\r" => '']);

        static::assertStringContainsString(
            "[ESCAPE][0;31mI\\Do\\Not\\Exist class not found\n".
            "[ESCAPE][0mPlease check your composer autoload is up to date and allow to load this class.\n",
            $content,
        );

        static::assertSame(
            "[ESCAPE][0;31mSpecified --base-directory is not a valid directory path.\n[ESCAPE][0m",
            strtr((string) static::getActionOutput(static function () use ($file, $template) {
                $cli = new SimpleCliCommand();
                $cli('simple-cli', 'build-phar', '--base-directory', '/i/do/not/exist');
            }), ["\033" => '[ESCAPE]', "\r" => '']),
        );

        static::assertSame(
            "[ESCAPE][0;31mEmpty list of class names and none found from scanning \"bin\" directory.\n[ESCAPE][0m",
            strtr((string) static::getActionOutput(static function () use ($file, $template) {
                $cli = new SimpleCliCommand();
                $cli('simple-cli', 'build-phar', '--bin-directory', '/i/do/not/exist');
            }), ["\033" => '[ESCAPE]', "\r" => '']),
        );
    }

    /**
     * @covers ::getVersionConstantDeclaration
     */
    public function testGetVersionConstantDeclaration(): void
    {
        $env = getenv('PHAR_PACKAGE_VERSION');
        putenv('PHAR_PACKAGE_VERSION=2.0.0');
        $version = $this->invoke(new BuildPhar(), 'getVersionConstantDeclaration');
        putenv("PHAR_PACKAGE_VERSION=$env");

        static::assertSame("\nconst SIMPLE_CLI_PHAR_PROGRAM_VERSION = '2.0.0';\n", $version);
    }
}
