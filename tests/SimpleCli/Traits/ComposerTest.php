<?php

namespace Tests\SimpleCli\Traits;

use SimpleCli\Composer\InstalledPackage;
use Tests\SimpleCli\DemoApp\DemoCli;

/**
 * @coversDefaultClass \SimpleCli\Traits\Composer
 *
 * @SuppressWarnings(PHPMD.ErrorControlOperator)
 */
class ComposerTest extends TraitsTestCase
{
    /**
     * @covers ::getPackageName
     */
    public function testGetPackageName(): void
    {
        $command = new DemoCli();

        static::assertSame('', $command->getPackageName());
    }

    /**
     * @covers ::getVendorDirectory
     */
    public function testGetVendorDirectory(): void
    {
        $command = new DemoCli();

        static::assertSame(realpath(__DIR__.'/../../../..'), realpath($command->getVendorDirectory()));
    }

    /**
     * @covers ::setVendorDirectory
     */
    public function testSetVendorDirectory(): void
    {
        /**
         * @var string $path
         */
        $path = realpath(__DIR__);
        $command = new DemoCli();
        $command->setVendorDirectory($path);

        static::assertSame($path, $command->getVendorDirectory());
    }

    /**
     * @covers ::getInstalledPackages
     */
    public function testGetInstalledPackages(): void
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
        @unlink($vendorDirectory.'/composer/installed.php');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        static::assertSame($packages, $command->getInstalledPackages());

        unlink($vendorDirectory.'/composer/installed.json');

        static::assertSame([], $command->getInstalledPackages());

        $packages = [
            'versions' => [
                'foo/bar' => [
                    'pretty_version' => '1.2.3',
                ],
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents(
            $vendorDirectory.'/composer/installed.php',
            '<?php return '.var_export($packages, true).';'
        );

        static::assertSame($packages['versions'], $command->getInstalledPackages());

        unlink($vendorDirectory.'/composer/installed.php');

        @rmdir($vendorDirectory.'/composer');
    }

    /**
     * @covers ::getInstalledPackage
     * @covers \SimpleCli\Composer\InstalledPackage::__construct
     */
    public function testGetInstalledPackage(): void
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
        @unlink($vendorDirectory.'/composer/installed.php');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        /**
         * @var InstalledPackage $installedPackage
         */
        $installedPackage = $command->getInstalledPackage('foo/bar');
        static::assertInstanceOf(InstalledPackage::class, $installedPackage);
        static::assertSame('foo/bar', $installedPackage->name);
        static::assertSame('1.2.3', $installedPackage->version);
        static::assertNull($command->getInstalledPackage('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.json');

        $packages = [
            'versions' => [
                'foo/bar' => [
                    'pretty_version' => '1.2.3',
                ],
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents(
            $vendorDirectory.'/composer/installed.php',
            '<?php return '.var_export($packages, true).';'
        );

        /**
         * @var InstalledPackage $installedPackage
         */
        $installedPackage = $command->getInstalledPackage('foo/bar');
        static::assertInstanceOf(InstalledPackage::class, $installedPackage);
        static::assertSame('foo/bar', $installedPackage->name);
        static::assertSame('1.2.3', $installedPackage->version);
        static::assertNull($command->getInstalledPackage('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.php');

        @rmdir($vendorDirectory.'/composer');
    }

    /**
     * @covers ::getInstalledPackageVersion
     */
    public function testGetInstalledPackageVersion(): void
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
        @unlink($vendorDirectory.'/composer/installed.php');
        file_put_contents($vendorDirectory.'/composer/installed.json', json_encode($packages));

        static::assertSame('1.2.3', $command->getInstalledPackageVersion('foo/bar'));
        static::assertSame('unknown', $command->getInstalledPackageVersion('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.json');

        $packages = [
            'versions' => [
                'foo/bar' => [
                    'pretty_version' => '1.2.3',
                ],
            ],
        ];

        $command = new DemoCli();
        $vendorDirectory = sys_get_temp_dir();
        $command->setVendorDirectory($vendorDirectory);

        @mkdir($vendorDirectory.'/composer');
        file_put_contents(
            $vendorDirectory.'/composer/installed.php',
            '<?php return '.var_export($packages, true).';'
        );

        static::assertSame('1.2.3', $command->getInstalledPackageVersion('foo/bar'));
        static::assertSame('unknown', $command->getInstalledPackageVersion('foo/biz'));

        unlink($vendorDirectory.'/composer/installed.php');

        @rmdir($vendorDirectory.'/composer');
    }
}
