<?php

declare(strict_types=1);

namespace SimpleCli\Options;

use SimpleCli\Attribute\Option;

trait Verbose
{
    #[Option('If this option is set, extra debug information will be displayed.')]
    public bool $verbose = false;
}
