<?php

namespace Tests\SimpleCli;

use Closure;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    public function assertOutput(string $expectedOutput, Closure $action)
    {
        ob_start();
        $action();
        $actualOutput = ob_get_contents();
        ob_end_clean();

        static::assertSame($expectedOutput, $actualOutput, "Output should be: $expectedOutput");
    }
}
