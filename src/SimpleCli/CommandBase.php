<?php

declare(strict_types=1);

namespace SimpleCli;

use SimpleCli\Options\Help;

abstract class CommandBase implements Command
{
    use Help;
}
