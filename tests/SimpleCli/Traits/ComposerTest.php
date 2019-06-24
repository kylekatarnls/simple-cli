<?php

namespace Tests\SimpleCli\Traits;

use SimpleCli\Composer\InstalledPackage;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Traits\Composer
 */
class ComposerTest extends TestCase
{
    /**
     * @covers ::getPackageName
     */
    public function testGetPackageName()
    {
        $command = new DemoCli();

        static::assertSame('', $command->getPackageName());
    }

    /**
     * @covers ::getVendorDirectory
     */
    public function testGetVendorDirectory()
    {
        $command = new DemoCli();

        static::assertSame(realpath(__DIR__.'/../../../..'), realpath($command->getVendorDirectory()));
    }

    /**
     * @covers ::setVendorDirectory
     */
    public function testSetVendorDirectory()
    {
        $path = realpath(__DIR__);
        $command = new DemoCli();
        $command->setVendorDirectory($path);

        static::assertSame($path, $command->getVendorDirectory());
    }

    /**
     * @covers ::getInstalledPackages
     */
    public function testGetInstalledPackages()
    {
        $packages = [
            [
                'name'    => 'foo/bar',
                'version' => '1.2.3',
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        static::assertSame($packages, $command->getInstalledPackages());

        unlink($vendorDirectory.'/composer/installed.json');
        @rmdir($vendorDirectory.'/composer');
    }

    /**
     * @covers ::getInstalledPackage
     * @covers \SimpleCli\Composer\InstalledPackage::__construct
     */
    public function testGetInstalledPackage()
    {
        $packages = [
            [
                'name'    => 'foo/bar',
                'version' => '1.2.3',
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        $installedPackage = $command->getInstalledPackage('foo/bar');
        static::assertInstanceOf(InstalledPackage::class, $installedPackage);
        static::assertSame('foo/bar', $installedPackage->name);
        static::assertSame('1.2.3', $installedPackage->version);
        static::assertNull($command->getInstalledPackage('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.json');
        @rmdir($vendorDirectory.'/composer');
    }

    /**
     * @covers ::getInstalledPackageVersion
     */
    public function testGetInstalledPackageVersion()
    {
        $packages = [
            [
                'name'    => 'foo/bar',
                'version' => '1.2.3',
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        static::assertSame('1.2.3', $command->getInstalledPackageVersion('foo/bar'));
        static::assertSame('unknown', $command->getInstalledPackageVersion('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.json');
        @rmdir($vendorDirectory.'/composer');
    }
}
