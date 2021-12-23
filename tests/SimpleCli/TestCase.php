<?php

namespace Tests\SimpleCli;

use Closure;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class TestCase extends FrameworkTestCase
{
    /**
     * @param object $object
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected static function invoke(object $object, string $method, ...$arguments): mixed
    {
        $reflection = (new ReflectionClass(get_class($object)))->getMethod($method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }

    /**
     * @param object $object
     * @param string $propertyName
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected static function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = (new ReflectionClass(get_class($object)))->getProperty($propertyName);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    protected static function revealWhiteCharacters(string $output): string
    {
        return strtr($output, [
            "\r" => "\\r\n",
            "\n" => "\\n\n",
            "\t" => '————',
        ]);
    }

    protected static function getActionOutput(Closure $action): ?string
    {
        ob_start();
        $action();
        $actualOutput = ob_get_contents();
        ob_end_clean();

        return $actualOutput === false ? null : $actualOutput;
    }

    public static function assertOutput(string $expectedOutput, Closure $action): void
    {
        static::assertSame(
            static::revealWhiteCharacters($expectedOutput),
            static::revealWhiteCharacters(self::getActionOutput($action) ?? ''),
            "Output should be: $expectedOutput",
        );
    }

    public static function assertOutputContains(string $needle, Closure $action): void
    {
        static::assertStringContainsString(
            static::revealWhiteCharacters($needle),
            static::revealWhiteCharacters(self::getActionOutput($action) ?? ''),
            "Output should contain: $needle",
        );
    }

    public static function assertFileContentEquals(string $expected, string $file, ?string $message = null): void
    {
        $message = "$file content should mismatch.".($message ? "\n$message" : '');

        static::assertSame($expected, str_replace("\r", '', file_get_contents($file) ?: ''), $message);
    }
}
