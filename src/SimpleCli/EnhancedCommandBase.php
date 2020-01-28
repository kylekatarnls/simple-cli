<?php

declare(strict_types=1);

namespace SimpleCli;

use SimpleCli\Options\Quiet;
use SimpleCli\Options\Verbose;

abstract class EnhancedCommandBase extends CommandBase
{
    use Quiet;
    use Verbose;
}
