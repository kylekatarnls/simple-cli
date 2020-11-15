<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand;

use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Quiet;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * Create a program in the bin directory that call the class given as argument.
 * Argument should be a class name (with namespace) that extends SimpleCli\SimpleCli.
 * Note that you must escape it, e.g. MyNamespace\\MyClass.
 */
class Create implements Command
{
    use Help;
    use Quiet;
    use Verbose;

    /**
     * List of program classes to convert into executable CLI programs.
     *
     * @rest
     *
     * @var string[]
     */
    public $classNames = [];

    /**
     * @param SimpleCli $cli
     *
     * @return bool
     */
    public function run(SimpleCli $cli): bool
    {
        if (!$this->ensureBinDirectoryExists()) {
            return $this->error($cli, 'Unable to create the bin directory');
        }

        $count = 0;

        foreach ($this->classNames as $className) {
            if ($this->verbose) {
                $cli->writeLine("Creating program for $className", 'light_cyan');
            }

            if (!class_exists($className)) {
                $this->error($cli, "$className class not found");
                $cli->writeLine('Please check your composer autoload is up to date and allow to load this class.');

                continue;
            }

            if (!is_subclass_of($className, SimpleCli::class)) {
                $this->error($cli, "$className needs to implement ".SimpleCli::class);

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
                '\\'.ltrim($className, '\\')
            );

            $count++;
        }

        $program = $count === 1 ? 'program' : 'programs';

        $cli->writeLine("$count $program created.", 'cyan');

        return $count > 0;
    }

    /**
     * @param SimpleCli $cli
     * @param string    $text
     *
     * @return bool
     */
    protected function error(SimpleCli $cli, $text): bool
    {
        $cli->writeLine($text, 'red');

        return false;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function extractName($className): string
    {
        $parts = explode('\\', $className);

        return trim(
            (string) preg_replace_callback(
                '/[A-Z]/',
                function (array $match) {
                    return '-'.strtolower($match[0]);
                },
                (string) end($parts)
            ),
            '-'
        );
    }

    protected function copyBinTemplate(SimpleCli $cli, string $name, string $className): void
    {
        $binTemplate = __DIR__.'/../../bin-template';

        foreach (scandir($binTemplate) ?: [] as $file) {
            if (substr($file, 0, 1) !== '.') {
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
                        ]
                    )
                );
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     *
     * @return bool
     */
    protected function ensureBinDirectoryExists(): bool
    {
        return is_dir('bin') || @mkdir('bin');
    }
}
