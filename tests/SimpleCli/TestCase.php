<?php

namespace Tests\SimpleCli;

use Closure;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionClass;

abstract class TestCase extends FrameworkTestCase
{
    protected static function invoke($object, string $method, ...$arguments)
    {
        $reflection = (new ReflectionClass(get_class($object)))->getMethod($method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }

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

    public static function assertOutput(string $expectedOutput, Closure $action)
    {
        ob_start();
        $action();
        $actualOutput = ob_get_contents();
        ob_end_clean();

        static::assertSame(
            static::revealWhiteCharacters($expectedOutput),
            static::revealWhiteCharacters($actualOutput),
            "Output should be: $expectedOutput"
        );
    }

    public static function assertFileContentEquals($expected, $file, $message = null)
    {
        $message = "$file content should mismatch.".($message ? "\n$message" : '');

        static::assertSame($expected, str_replace("\r", '', file_get_contents($file) ?: ''), $message);
    }
}
