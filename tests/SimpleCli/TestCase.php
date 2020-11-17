<?php

namespace Tests\SimpleCli;

use Closure;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionClass;
use ReflectionException;

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
    protected static function invoke($object, string $method, ...$arguments)
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
    protected static function getPropertyValue($object, string $propertyName)
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

    public static function assertOutput(string $expectedOutput, Closure $action): void
    {
        ob_start();
        $action();
        $actualOutput = ob_get_contents();
        ob_end_clean();

        static::assertSame(
            static::revealWhiteCharacters($expectedOutput),
            static::revealWhiteCharacters($actualOutput ?: ''),
            "Output should be: $expectedOutput"
        );
    }

    public static function assertFileContentEquals(string $expected, string $file, ?string $message = null): void
    {
        $message = "$file content should mismatch.".($message ? "\n$message" : '');

        static::assertSame($expected, str_replace("\r", '', file_get_contents($file) ?: ''), $message);
    }
}
