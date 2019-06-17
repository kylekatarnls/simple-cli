<?php

namespace SimpleCli\SimpleCliCommand;

use ReflectionProperty;
use SimpleCli\Command;
use SimpleCli\SimpleCli;

class Create implements Command
{
    protected function error(SimpleCli $cli, $text)
    {
        $cli->writeLine($text, 'red');

        return false;
    }

    protected function extractName($className)
    {
        $parts = explode('\\', $className);

        return trim(preg_replace_callback('/[A-Z]/', function (array $match) {
            return '-'.strtolower($match[0]);
        }, end($parts)), '-');
    }

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
            try {
                $cli = new $className();
                $name = new ReflectionProperty($cli, 'name');
                $name->setAccessible(true);
                $name = $name->getValue($cli);
            } catch (\ReflectionException $e) {
                $name = null;
            }

            if (empty($name)) {
                $name = $this->extractName($className);
            }

            $className = '\\'.ltrim($className, '\\');
            $binTemplate = __DIR__.'/../../bin-template';

            foreach (scandir($binTemplate) as $file) {
                if (substr($file, 0, 1) !== '.') {
                    file_put_contents(
                        'bin/'.str_replace('program', $name, $file),
                        strtr(file_get_contents("$binTemplate/$file"), [
                            '{name}' => $name,
                            '{class}' => $className,
                        ])
                    );
                }
            }

            $count++;
        }

        $program = $count === 1 ? 'program' : 'programs';

        $cli->writeLine("$count $program created.", 'cyan');

        return $count > 0;
    }
}
