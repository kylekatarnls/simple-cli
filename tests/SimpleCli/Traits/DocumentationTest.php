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
    public function testExtractClassNameDescription(): void
    {
        $command = new DemoCli();

        static::assertSame('This is a demo.', $command->extractClassNameDescription(DemoCommand::class));
        static::assertSame('stdClass', $command->extractClassNameDescription(stdClass::class));
        /** @phpstan-var class-string $notFound */
        $notFound = 'NotFound';
        static::assertSame('NotFound', $command->extractClassNameDescription($notFound));
    }

    /**
     * @covers ::extractAnnotation
     */
    public function testExtractAnnotation(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            ['hello', 'hi', 'bye'],
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    static fn ($option) => $option['property'] === 'prefix',
                )
            )[0]['values'],
        );
    }

    /**
     * @covers ::cleanPhpDocComment
     */
    public function testCleanPhpDocComment(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            'Append a prefix to $sentence.',
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    static fn ($option) => $option['property'] === 'prefix',
                )
            )[0]['description'],
        );
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     * @covers ::extractOptionInfo
     * @covers ::extractArgumentInfo
     * @covers ::getValues
     */
    public function testAddExpectation(): void
    {
        $command = new DemoCli();
        $command->mute();

        $command('file', 'foobar');

        static::assertSame(
            ['prefix', 'p'],
            array_values(
                array_filter(
                    $command->getExpectedOptions(),
                    static fn ($option) => $option['property'] === 'prefix',
                )
            )[0]['names'],
        );

        static::assertSame(
            'Sentence to display.',
            array_values(
                array_filter(
                    $command->getExpectedArguments(),
                    static fn ($argument) => $argument['property'] === 'sentence',
                )
            )[0]['description'],
        );

        $command('file', 'create');

        static::assertSame('classNames', $command->getExpectedRestArgument()['property'] ?? null);
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     * @covers ::extractOptionInfo
     * @covers ::extractArgumentInfo
     * @covers ::getValues
     */
    public function testAddExpectationCast(): void
    {
        static::assertOutput(
            "9\nA|B|C\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', 'A', 'B', 'C');

                static::assertSame('string', $command->getExpectedRestArgument()['type'] ?? null);
            },
        );
    }

    /**
     * @covers ::addExpectation
     * @covers ::concatDescription
     * @covers ::extractOptionInfo
     * @covers ::extractArgumentInfo
     * @covers ::getValues
     */
    public function testAddExpectationInvalidKind(): void
    {
        static::assertOutput(
            'A property cannot be both #Option / @option and #Argument / @argument',
            static function () {
                $command = new DemoCli();
                $command->disableColors();

                $command('file', 'bad');
            },
        );
    }

    /**
     * @covers ::extractExpectations
     * @covers ::getAttributeOrAnnotation
     */
    public function testExtractExpectations(): void
    {
        static::assertOutput(
            '[ESCAPE][0;31mUnknown --foo option[ESCAPE][0m',
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--foo=12');
            },
        );

        static::assertOutput(
            "12\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--bar=12');
            },
        );

        static::assertOutput(
            "12\n\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'all', '--biz=12');
            },
        );

        static::assertOutput(
            "hi\n",
            static function () {
                $command = new DemoCli();

                $command('file', 'foobar', '--prefix=hi');
            },
        );
    }

    /**
     * @covers ::getPropertyType
     * @covers ::getTypeAndRestDescription
     * @covers ::getTypeName
     * @covers ::normalizeScalarType
     */
    public function testPropertyTypeByVarAnnotation(): void
    {
        $command = new DemoCli();
        $mockFile = __DIR__.'/../SimpleCliCommand/VarAnnotation.php';
        $originalContent = (string) file_get_contents($mockFile);
        file_put_contents($mockFile, str_replace("@var bool\n", "@var boolean\n", $originalContent));

        $this->invoke($command, 'extractExpectations', new VarAnnotation());

        file_put_contents($mockFile, $originalContent);

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }

    /**
     * @covers ::getPropertyType
     * @covers ::getTypeAndRestDescription
     * @covers ::getTypeName
     */
    public function testPropertyTypeByDefaultValue(): void
    {
        $command = new DemoCli();

        $this->invoke($command, 'extractExpectations', new DefaultValue());

        static::assertSame('float', static::getPropertyValue($command, 'expectedOptions')[0]['type']);
        static::assertSame('bool', static::getPropertyValue($command, 'expectedArguments')[0]['type']);
        static::assertSame('float|string', static::getPropertyValue($command, 'expectedRestArgument')['type']);
    }
}
