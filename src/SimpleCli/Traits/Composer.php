<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use SimpleCli\Composer\InstalledPackage;

trait Composer
{
    /**
     * Get the composer package name that handle the CLI program.
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return '';
    }

    /**
     * Get the list of packages installed with composer.
     *
     * @return array
     */
    public function getInstalledPackages()
    {
        $installedJson = __DIR__.'/../../../../composer/installed.json';
        $installedData = file_exists($installedJson)
            ? @json_decode((string) file_get_contents($installedJson), true)
            : null;

        return $installedData ?: [];
    }

    /**
     * Get data for a given installed package.
     *
     * @param string $name
     *
     * @return InstalledPackage|null
     */
    public function getInstalledPackage(string $name): ?InstalledPackage
    {
        foreach ($this->getInstalledPackages() as $package) {
            if (($package['name'] ?? null) === $name) {
                return new InstalledPackage($package);
            }
        }

        return null;
    }

    /**
     * Get the version of a given installed package.
     *
     * @param string $name
     *
     * @return string
     */
    public function getInstalledPackageVersion(string $name): string
    {
        $package = $this->getInstalledPackage($name);
        $version = $package ? $package->version : null;

        return $version ?: 'unknown';
    }
}
