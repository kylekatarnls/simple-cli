<?php

declare(strict_types=1);

namespace SimpleCli\Options;

use SimpleCli\Attribute\Option;

trait Quiet
{
    #[Option('If this option is set, the command will run silently (no output).')]
    public bool $quiet = false;
}
