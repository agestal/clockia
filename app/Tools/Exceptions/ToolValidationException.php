<?php

namespace App\Tools\Exceptions;

use RuntimeException;

class ToolValidationException extends RuntimeException
{
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
