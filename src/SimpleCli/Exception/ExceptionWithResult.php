<?php

declare(strict_types=1);

namespace SimpleCli\Exception;

interface ExceptionWithResult
{
    public function getResult(): mixed;
}
