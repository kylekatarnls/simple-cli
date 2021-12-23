<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Option;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;
use stdClass;

class AutoTypeDefaultsCommand implements Command
{
    use Help;

    #[Option]
    public $neutral;

    #[Option]
    public string $string;

    #[Option]
    public int $int;

    #[Option]
    public float $float;

    #[Option]
    public bool $bool;

    #[Option]
    public array $array;

    #[Option]
    public stdClass $stdClass;

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
