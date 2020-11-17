<?php

declare(strict_types=1);

namespace SimpleCli;

use SimpleCli\SimpleCliCommand\Create;

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
    protected $name = 'simple-cli';

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
            'create' => Create::class,
        ];
    }
}
