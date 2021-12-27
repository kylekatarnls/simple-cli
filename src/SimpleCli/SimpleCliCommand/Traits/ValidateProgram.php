<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand\Traits;

use SimpleCli\SimpleCli;
use SimpleCli\Writer;

trait ValidateProgram
{
    public function validateProgram(Writer $cli, string $className): bool
    {
        if (!class_exists($className)) {
            $cli->write("$className class not found\n", 'red');
            $cli->write("Please check your composer autoload is up to date and allow to load this class.\n");

            return false;
        }

        if (!is_subclass_of($className, SimpleCli::class)) {
            $cli->write("$className needs to implement ".SimpleCli::class."\n", 'red');

            return false;
        }

        return true;
    }

    protected function extractName(mixed $className): string
    {
        $parts = explode('\\', (string) $className);

        return trim(
            (string) preg_replace_callback(
                '/[A-Z]/',
                static fn (array $match) => '-'.strtolower($match[0]),
                end($parts),
            ),
            '-'
        );
    }
}
