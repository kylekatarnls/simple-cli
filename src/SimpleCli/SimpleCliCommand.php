<?php

declare(strict_types=1);

namespace SimpleCli;

use SimpleCli\SimpleCliCommand\Create;

class SimpleCliCommand extends SimpleCli
{
    protected $name = 'simple-cli';

    public function getPackageName(): string
    {
        return 'simple-cli/simple-cli';
    }

    public function getCommands(): array
    {
        return [
            'create' => Create::class,
        ];
    }
}