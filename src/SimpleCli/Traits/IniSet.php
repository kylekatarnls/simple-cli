<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use Phar;
use SimpleCli\SimpleCliOption;

trait IniSet
{
    public function iniSet(string $key, string|int|float|bool $expectedValue = true): ?bool
    {
        $value = ini_get($key);
        $originalValue = $value;

        if (is_bool($expectedValue)) {
            $value = ((int) $value) ? 'On' : 'Off';
            $expectedValue = $expectedValue ? 'On' : 'Off';
        } elseif (!is_string($expectedValue)) {
            $expectedValue = (string) $value;
        }

        if ($value !== $expectedValue) {
            $arguments = $GLOBALS['argv'] ?? [];

            if (in_array(SimpleCliOption::SKIP_INI_FIX, $arguments, true)) {
                $iniFile = php_ini_loaded_file() ?: 'php.ini';

                return $this->error("$key is $originalValue, set $key=$expectedValue in $iniFile and retry.");
            }

            $resultCode = null;

            passthru(
                PHP_BINARY." -d $key=$expectedValue ".
                escapeshellarg(
                    Phar::running(false) ?: get_included_files()[0],
                ).' '.
                implode(' ', array_map('escapeshellarg', [
                    ...array_slice($arguments, 1) ?: ['list'],
                    SimpleCliOption::SKIP_INI_FIX,
                ])),
                $resultCode,
            );

            return !$resultCode;
        }

        return null;
    }
}
