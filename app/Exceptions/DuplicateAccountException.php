<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateAccountException extends RuntimeException
{
    /** @param 'email' $field */
    public function __construct(public readonly string $field)
    {
        parent::__construct("Duplicate account field: {$field}");
    }
}
