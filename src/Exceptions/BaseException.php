<?php

namespace Desq\TestOrderProcessor\Exceptions;

use Exception;

class BaseException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        Exception $previous = null
    ) {
        $message = static::class . ": " . $message;

        parent::__construct($message, $code, $previous);
    }
}
