<?php

namespace Tests\SimpleCli\Options;

use Tests\SimpleCli\DemoApp\UnionCli;
use Tests\SimpleCli\TestCase;

/**
 * @coversDefaultClass \SimpleCli\Trait\Documentation
 */
class UnionTypeTest extends TestCase
{
    /**
     * @covers ::getTypeName
     */
    public function testUnionType(): void
    {
        static::assertOutput(
            <<<'EOS'
            [ESCAPE][0;33mUsage:
            [ESCAPE][0m  union union [options] 

            [ESCAPE][0;33mOptions:
            [ESCAPE][0m  [ESCAPE][0;32m-i, --input[ESCAPE][0m  
                           [ESCAPE][0;36mstring|int|float[ESCAPE][0m[ESCAPE][0;33mdefault: ''[ESCAPE][0m
              [ESCAPE][0;32m-h, --help[ESCAPE][0m   Display documentation of the current command.
                           [ESCAPE][0;36mbool            [ESCAPE][0m[ESCAPE][0;33mdefault: false[ESCAPE][0m
            
            EOS,
            static function () {
                $command = new UnionCli();

                $command('union', 'union', '--help');
            },
        );
    }
}
