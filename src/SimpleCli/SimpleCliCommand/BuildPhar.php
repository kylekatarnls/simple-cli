<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand;

use Generator;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\Rest;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Quiet;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;
use SimpleCli\SimpleCliCommand\Traits\ValidateProgram;
use SimpleCli\Widget\ProgressBar;
use SplFileInfo;
use Throwable;

/**
 * Build the current program as a phar file.
 */
class BuildPhar implements Command
{
    use Help;
    use Quiet;
    use ValidateProgram;
    use Verbose;

    #[Option(
        'Where to build PHAR files, current directory is used if not set.',
        alias: ['b', 'd'],
    )]
    public string $baseDirectory;

    #[Option('Name of the main file of the PHAR.')]
    public string $mainFileName = 'main.php';

    #[Option('Output file')]
    public string $outputFile = '';

    #[Option('Where to search for programs (if no explicit classes passed as arguments).')]
    public string $binDirectory = 'bin';

    /** @var string[] */
    #[Rest('List of program classes to build as PHAR files.')]
    public array $classNames = [];

    protected ProgressBar $progressBar;
    protected int $total;
    protected int $index;

    public function run(SimpleCli $cli): bool
    {
        if (!class_exists(Phar::class)) {
            return $cli->error('Phar extension is disabled, install and enable it to run this command.');
        }

        $this->baseDirectory = realpath(($this->baseDirectory ?? getcwd()) ?: '.');

        if (!$this->baseDirectory || !is_dir($this->baseDirectory)) {
            return $cli->error('Specified --base-directory is not a valid directory path.');
        }

        $this->baseDirectory .= DIRECTORY_SEPARATOR;
        $classNames = $this->getClassNames();

        if (!$classNames) {
            return $cli->error('Empty list of class names and none found from scanning "bin" directory.');
        }

        $handled = $cli->iniSet('phar.readonly', false);

        if ($handled !== null) {
            return $handled;
        }

        $count = 0;
        $this->progressBar = new ProgressBar($cli);
        $this->progressBar->start();
        $this->total = count($classNames);

        foreach ($classNames as $index => $className) {
            $this->index = $index;
            $this->setSubStep(0.0);

            if ($this->verbose) {
                $cli->writeLine("Building program for $className", 'light_cyan');
            }

            if (!$this->validateProgram($cli, $className)) {
                $this->setSubStep(0.5);

                continue;
            }

            /**
             * @psalm-suppress UnsafeInstantiation
             *
             * @var SimpleCli $createdCli
             */
            $createdCli = new $className();
            $this->buildPhar($className, $createdCli->getName() ?: $this->extractName($className));

            $count++;
        }

        $this->progressBar->setValue(1.0);
        $this->progressBar->end();

        $program = $count === 1 ? 'program' : 'programs';

        $cli->writeLine("$count $program built.", 'cyan');

        return $count > 0;
    }

    protected function buildPhar(string $className, string $name): bool
    {
        if (!str_starts_with($className, '\\')) {
            $className = "\\$className";
        }

        $directories = [];

        try {
            $class = new ReflectionClass($className);
            $file = $class->getFileName();

            if (str_starts_with($file, $this->baseDirectory)) {
                $pos = strpos($file, DIRECTORY_SEPARATOR, strlen($this->baseDirectory));

                if ($pos !== false) {
                    $directories[] = substr($file, 0, $pos);
                }
            }
        } catch (Throwable) {
            // Empty $directories list
        }

        $pharFile = $this->baseDirectory."$name.phar";
        $mainFile = $this->baseDirectory.$this->mainFileName;

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        if (file_exists($pharFile.'.gz')) {
            unlink($pharFile.'.gz');
        }

        $this->setSubStep(0.1);
        $phar = new Phar($pharFile);

        $phar->startBuffering();
        $this->setSubStep(0.2);

        file_put_contents($mainFile, strtr(<<<'EOS'
            <?php

            declare(strict_types=1);

            include __DIR__ . '/vendor/autoload.php';
            {versionConstant}
            exit((new {className}())(...$argv) ? 0 : 1);

            EOS, [
            '{className}'       => $className,
            '{versionConstant}' => $this->getVersionConstantDeclaration(),
        ]));
        $this->setSubStep(0.3);

        $defaultStub = $phar->createDefaultStub($this->mainFileName);
        $this->setSubStep(0.35);

        $phar->buildFromIterator($this->getFiles($mainFile, $directories), $this->baseDirectory);
        $this->setSubStep(0.65);

        $success = $phar->setStub("#!/usr/bin/env php\n$defaultStub");
        $this->setSubStep(0.7);

        $phar->stopBuffering();
        $this->setSubStep(0.75);

        $phar->compressFiles(Phar::GZ);
        $this->setSubStep(0.85);

        chmod($pharFile, 0777);
        $this->setSubStep(0.9);

        unlink($mainFile);
        $this->setSubStep(0.95);

        return $success;
    }

    protected function setSubStep(float $step): void
    {
        $this->progressBar->setValue(($this->index + $step) / $this->total);
    }

    /**
     * @param string[] $directories
     *
     * @return Generator<SplFileInfo>
     */
    protected function getPaths(array $directories = []): Generator
    {
        array_push($directories, ...array_map(
            'realpath',
            [$this->baseDirectory.'/src', $this->baseDirectory.'/vendor'],
        ));

        foreach (array_unique($directories) as $folder) {
            yield from new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
        }
    }

    /**
     * @param string   $mainFile
     * @param string[] $directories
     *
     * @return Generator<SplFileInfo>
     */
    protected function getFiles(string $mainFile, array $directories = []): Generator
    {
        yield new SplFileInfo($mainFile);

        foreach ($this->getPaths($directories) as $path) {
            if ($path->isFile()) {
                yield $path;
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getClassNames(): array
    {
        $classNames = $this->classNames;

        if (!$classNames) {
            $programs = is_dir($this->binDirectory) ? scandir($this->binDirectory) : [];

            foreach ($programs as $file) {
                $path = $this->binDirectory."/$file";

                if (!is_file($path) || $file === 'build-api-reference.php') {
                    continue;
                }

                foreach (token_get_all(file_get_contents($path)) as $token) {
                    if (is_array($token) &&
                        $token[0] === T_NAME_FULLY_QUALIFIED &&
                        is_a($token[1], SimpleCli::class, true)
                    ) {
                        $classNames[] = $token[1];
                    }
                }
            }
        }

        return $classNames;
    }

    protected function getVersionConstantDeclaration(): string
    {
        $version = getenv('PHAR_PACKAGE_VERSION');

        if (is_string($version) && preg_match('`^(.*/)?v?(?<version>\d[^/]*)$`', $version, $match)) {
            $version = var_export($match['version'], true);

            return "\nconst SIMPLE_CLI_PHAR_PROGRAM_VERSION = $version;\n";
        }

        return '';
    }
}
