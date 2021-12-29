<?php

declare(strict_types=1);

namespace SimpleCli\Exception;

use Throwable;

interface ExceptionWithResult extends Throwable
{
    public function getResult(): mixed;
}
