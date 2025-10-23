<?php

namespace App\Exceptions;

use Exception;

class CompteNotFoundException extends Exception
{
    public function __construct($message = 'Compte not found', $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}