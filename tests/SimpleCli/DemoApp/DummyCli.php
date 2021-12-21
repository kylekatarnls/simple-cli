<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;

class DummyCli extends SimpleCli
{
    protected ?string $name = 'stupid';

    protected string $escapeCharacter = '[ESCAPE]';
}
