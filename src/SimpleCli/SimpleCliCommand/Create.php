<?php

namespace SimpleCli\SimpleCliCommand;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Create a program in the bin directory that call the class given as argument.
 * Argument should be a class name (with namespace) that extends SimpleCli\SimpleCli.
 * Note that you must escape it, e.g. MyNamespace\\MyClass.
 */
class Create implements Command
{
    protected function error(SimpleCli $cli, $text): bool
    {
        $cli->writeLine($text, 'red');

        return false;
    }

    protected function extractName($className): string
    {
        $parts = explode('\\', $className);

        return trim(preg_replace_callback('/[A-Z]/', function (array $match) {
            return '-'.strtolower($match[0]);
        }, end($parts) ?: '') ?: '', '-');
    }

    protected function copyBinTemplate(string $name, string $className): void
    {
        $binTemplate = __DIR__.'/../../bin-template';

        foreach (scandir($binTemplate) ?: [] as $file) {
            if (substr($file, 0, 1) !== '.') {
                file_put_contents(
                    'bin/'.str_replace('program', $name, $file),
                    strtr(file_get_contents("$binTemplate/$file") ?: '', [
                        '{name}'  => $name,
                        '{class}' => $className,
                    ])
                );
            }
        }
    }

    /**
     * @param SimpleCli $cli
     * @param string[]  ...$parameters
     *
     * @return bool
     */
    public function run(SimpleCli $cli, ...$parameters): bool
    {
        if (!is_dir('bin') && !@mkdir('bin')) {
            return $this->error($cli, 'Unable to create the bin directory');
        }

        $count = 0;

        foreach ($parameters as $className) {
            if (!is_subclass_of($className, SimpleCli::class)) {
                $this->error($cli, "$className needs to implement".SimpleCli::class);

                continue;
            }

            $this->copyBinTemplate(
                $cli->getName() ?: $this->extractName($className),
                '\\'.ltrim($className, '\\')
            );

            $count++;
        }

        $program = $count === 1 ? 'program' : 'programs';

        $cli->writeLine("$count $program created.", 'cyan');

        return $count > 0;
    }
}
