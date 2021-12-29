<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

use Phar;
use SimpleCli\SimpleCliOption;
use SimpleCli\Writer;

trait IniSet
{
    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param string                $key
     * @param string|int|float|bool $expectedValue
     *
     * @return bool|null
     */
    public function iniSet(string $key, string|int|float|bool $expectedValue = true): ?bool
    {
        $value = ini_get($key);
        $originalValue = $value;
        [$value, $expectedValue] = $this->formatValues($value, $expectedValue);

        if ($value !== $expectedValue) {
            /** @var list<string> $arguments */
            $arguments = $GLOBALS['argv'] ?? []; // phpcs:disable SuperglobalSniff

            if (in_array(SimpleCliOption::SKIP_INI_FIX, $arguments, true)) {
                $iniFile = php_ini_loaded_file() ?: 'php.ini';

                if ($this instanceof Writer) {
                    $this->write("$key is $originalValue, set $key=$expectedValue in $iniFile and retry.\n", 'red');
                }

                return false;
            }

            $resultCode = null;

            // @phan-suppress-next-line PhanUndeclaredProperty
            ($this->passthruFunction ?? 'passthru')(
                PHP_BINARY." -d $key=$expectedValue ".
                escapeshellarg(
                    Phar::running(false) ?: get_included_files()[0],
                ).' '.
                implode(' ', array_map('escapeshellarg', [
                    ...(array_slice($arguments, 1) ?: ['list']),
                    SimpleCliOption::SKIP_INI_FIX,
                ])),
                $resultCode,
            );

            return !$resultCode;
        }

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string|bool           $value
     * @param string|int|float|bool $expectedValue
     *
     * @return array
     */
    private function formatValues(string|bool $value, string|int|float|bool $expectedValue = true): array
    {
        if (is_bool($expectedValue)) {
            return [
                ((int) $value) ? 'On' : 'Off',
                $expectedValue ? 'On' : 'Off',
            ];
        }

        if (!is_string($expectedValue)) {
            return [
                $value,
                (string) $expectedValue,
            ];
        }

        return [
            $value,
            $expectedValue,
        ];
    }
}
