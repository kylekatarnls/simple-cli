<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\SimpleCli;

class DummyCli extends SimpleCli
{
    protected $name = 'stupid';

    protected $escapeCharacter = '[ESCAPE]';
}
