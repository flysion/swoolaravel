<?php
namespace Lee2son\Swoolaravel\Exceptions;

use Throwable;

class InvalidEventException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}