<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use SimpleCli\Composer\InstalledPackage;

trait Composer
{
    /**
     * @var string
     */
    protected $vendorDirectory = __DIR__.'/../../../..';

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
     * Set the vendor that should contains packages including composer/installed.json.
     *
     * @param string $vendorDirectory
     */
    public function setVendorDirectory(string $vendorDirectory): void
    {
        $this->vendorDirectory = $vendorDirectory;
    }

    /**
     * Get the vendor that should contains packages including composer/installed.json.
     *
     * @return string
     */
    public function getVendorDirectory(): string
    {
        return $this->vendorDirectory;
    }

    /**
     * Get the list of packages installed with composer.
     *
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     *
     * @return array<string|int, array<string, string>>
     */
    public function getInstalledPackages()
    {
        $composerDirectory = $this->getVendorDirectory().'/composer';

        if (file_exists($composerDirectory.'/installed.php')) {
            /** @psalm-suppress UnresolvableInclude */
            $installedData = (@include $composerDirectory.'/installed.php') ?: [];
            $installedData = $installedData['versions'] ?? $installedData;

            if (!empty($installedData)) {
                return $installedData;
            }
        }

        $installedJson = $composerDirectory.'/installed.json';
        $installedData = file_exists($installedJson)
            ? @json_decode((string) file_get_contents($installedJson), true)
            : null;
        $installedData = $installedData ?: [];

        return $installedData['packages'] ?? $installedData;
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
        foreach ($this->getInstalledPackages() as $key => $package) {
            if (!isset($package['name']) && is_string($key)) {
                $package['name'] = $key;
                $package['version_normalized'] = $package['version'] ?? $package['pretty_version'] ?? 'unknown';
                $package['version'] = $package['pretty_version'] ?? $package['version'] ?? 'unknown';
            }

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
