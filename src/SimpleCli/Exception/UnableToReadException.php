<?php

declare(strict_types=1);

namespace SimpleCli\Exception;

use RuntimeException;

class UnableToReadException extends RuntimeException implements ExceptionWithResult
{
    public function __construct(
        private mixed $result,
        string $expectedType = 'string',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $type = gettype($result);

        parent::__construct(
            "Unable to read, expected type was $expectedType but result type was $type",
            $code,
            $previous,
        );
    }

    public function getResult(): mixed
    {
        return $this->result;
    }
}
