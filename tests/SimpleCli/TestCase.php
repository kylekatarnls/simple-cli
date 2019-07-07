<?php

namespace Tests\SimpleCli;

use Closure;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected function invoke($object, string $method, ...$arguments)
    {
        $reflection = (new \ReflectionClass(get_class($object)))->getMethod($method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }

    public function assertOutput(string $expectedOutput, Closure $action)
    {
        ob_start();
        $action();
        $actualOutput = ob_get_contents();
        ob_end_clean();

        static::assertSame($expectedOutput, $actualOutput, "Output should be: $expectedOutput");
    }

    public static function assertFileContentEquals($expected, $file, $message = null)
    {
        $message = "$file content should mismatch.".($message ? "\n$message" : '');

        static::assertSame($expected, str_replace("\r", '', file_get_contents($file) ?: ''), $message);
    }
}
