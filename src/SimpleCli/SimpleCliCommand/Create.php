<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand;

use SimpleCli\Attribute\Rest;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Quiet;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;
use SimpleCli\SimpleCliCommand\Traits\ValidateProgram;

/**
 * Create a program in the bin directory that call the class given as argument.
 * Argument should be a class name (with namespace) that extends SimpleCli\SimpleCli.
 * Note that you must escape it, e.g. MyNamespace\\MyClass.
 */
class Create implements Command
{
    use Help;
    use Quiet;
    use ValidateProgram;
    use Verbose;

    /**
     * @var string[]
     *
     * @psalm-var class-string[]
     */
    #[Rest('List of program classes to convert into executable CLI programs.')]
    public array $classNames = [];

    public function run(SimpleCli $cli): bool
    {
        if (!$this->ensureBinDirectoryExists()) {
            return $cli->error('Unable to create the bin directory');
        }

        $count = 0;

        foreach ($this->classNames as $className) {
            if ($this->verbose) {
                $cli->writeLine("Creating program for $className", 'light_cyan');
            }

            if (!$this->validateProgram($cli, $className)) {
                continue;
            }

            /**
             * @psalm-suppress UnsafeInstantiation
             *
             * @var SimpleCli $createdCli
             */
            $createdCli = new $className();

            $this->copyBinTemplate(
                $cli,
                $createdCli->getName() ?: $this->extractName($className),
                '\\'.ltrim($className, '\\'),
            );

            $count++;
        }

        $program = $count === 1 ? 'program' : 'programs';

        $cli->writeLine("$count $program created.", 'cyan');

        return $count > 0;
    }

    protected function copyBinTemplate(SimpleCli $cli, string $name, string $className): void
    {
        $binTemplate = __DIR__.'/../../bin-template';

        foreach (scandir($binTemplate) ?: [] as $file) {
            if (!str_starts_with($file, '.')) {
                $path = 'bin/'.str_replace('program', $name, $file);

                if ($this->verbose) {
                    $cli->writeLine("Creating $path");
                }

                file_put_contents(
                    $path,
                    strtr(
                        (string) file_get_contents("$binTemplate/$file"),
                        [
                            '{name}'  => $name,
                            '{class}' => $className,
                        ],
                    ),
                );
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    protected function ensureBinDirectoryExists(): bool
    {
        return is_dir('bin') || @mkdir('bin');
    }
}
