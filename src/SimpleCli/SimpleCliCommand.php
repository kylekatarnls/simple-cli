<?php

declare(strict_types=1);

namespace SimpleCli;

use SimpleCli\SimpleCliCommand\BuildPhar;
use SimpleCli\SimpleCliCommand\Create;
use SimpleCli\SimpleCliCommand\Palette;

/**
 * Class SimpleCliCommand.
 *
 * @property string[] $parameters
 * @property array[]  $arguments
 * @property array[]  $expectedArguments
 * @property array[]  $restArguments
 * @property array    $options
 * @property array[]  $expectedOptions
 */
class SimpleCliCommand extends SimpleCli
{
    protected ?string $name = 'simple-cli';

    public function getPackageName(): string
    {
        return 'simple-cli/simple-cli';
    }

    /**
     * @return array<string, class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'build-phar' => BuildPhar::class,
            'create'     => Create::class,
            'palette'    => Palette::class,
        ];
    }
}
