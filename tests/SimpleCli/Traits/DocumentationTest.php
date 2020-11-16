<?php

namespace Tests\SimpleCli\Traits;

use stdClass;
use Tests\SimpleCli\DemoApp\DemoCli;
use Tests\SimpleCli\DemoApp\DemoCommand;
use Tests\SimpleCli\SimpleCliCommand\DefaultValue;
use Tests\SimpleCli\SimpleCliCommand\VarAnnotation;

/**
 * @coversDefaultClass \SimpleCli\Traits\Documentation
 */
class DocumentationTest extends TraitsTestCase
{
    /**
     * @covers ::extractClassNameDescription
     */
    public function testExtractClassNameDescription()
    {
        $command = new DemoCli();

        static::assertSame('This is a demo.', $command->extractClassNameDescription(DemoCommand::class));
        static::assertSame('stdClass', $command->extractClassNameDescription(stdClass::class));
        static::assertSame('NotFound', $command->extractClassNameDescription('NotFound'));
    }

    /**
     * @covers ::extractAnnotation
     */
    public function testExtractAnnotation()
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            'hello, hi, bye',
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    function ($option) {
                        return $option['property'] === 'prefix';
                    }
                )
            )[0]['values']
        );
    }

    /**
     * @covers ::cleanPhpDocComment
     */
    public function testCleanPhpDocComment()
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            'Append a prefix to $sentence.',
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    function ($option) {
                        return $option['property'] === 'prefix';
                    }
                )
            )[0]['description']
        );
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     */
    public function testAddExpectation()
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            ['prefix', 'p'],
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    function ($option) {
                        return $option['property'] === 'prefix';
                    }
                )
            )[0]['names']
        );

        static::assertSame(
            'Sentence to display.',
            array_values(
                array_filter(
                    $command->getExpectedArguments(),
                    function ($argument) {
                        return $argument['property'] === 'sentence';
                    }
                )
            )[0]['description']
        );

        $command('file', 'create');

        static::assertSame('classNames', $command->getExpectedRestArgument()['property']);
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     */
    public function testAddExpectationCast()
    {
        static::assertOutput(
            "9\nA|B|C\n",
            function () {
                $command = new DemoCli();

                $command('file', 'all', 'A', 'B', 'C');

                static::assertSame('string', $command->getExpectedRestArgument()['type']);
            }
        );
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     */
    public function testAddExpectationInvalidKind()
    {
        static::assertOutput(
            'A property cannot be both @option and @argument',
            function () {
                $command = new DemoCli();
                $command->disableColors();

                $command('file', 'bad');
            }
        );
    }

    /**
     * @covers ::extractExpectations
     */
    public function testExtractExpectations()
    {
        static::assertOutput(
            '[ESCAPE][0;31mUnknown --foo option[ESCAPE][0m',
            function () {
                $command = new DemoCli();

                $command('file', 'all', '--foo=12');
            }
        );

        static::assertOutput(
            "12\n\n",
            function () {
                $command = new DemoCli();

                $command('file', 'all', '--bar=12');
            }
        );

        static::assertOutput(
            "12\n\n",
            function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz=12');
            }
        );

        static::assertOutput(
            "hi\n",
            function () {
                $command = new DemoCli();

                $command('file', 'foobar', '--prefix=hi');
            }
        );
    }

    /**
     * @covers ::getPropertyType
     * @covers ::getPropertyTypeByHint
     * @covers ::normalizeScalarType
     */
    public function testPropertyTypeByVarAnnotation()
    {
        $command = new DemoCli();
        $mockFile = __DIR__.'/../SimpleCliCommand/VarAnnotation.php';
        $originalContent = file_get_contents($mockFile);
        file_put_contents($mockFile, str_replace("@var bool\n", "@var boolean\n", $originalContent));

        $this->invoke($command, 'extractExpectations', new VarAnnotation());

        file_put_contents($mockFile, $originalContent);

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }

    /**
     * @covers ::getPropertyType
     * @covers ::getPropertyTypeByHint
     */
    public function testPropertyTypeByDefaultValue()
    {
        $command = new DemoCli();

        $this->invoke($command, 'extractExpectations', new DefaultValue());

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float|string', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }
}
