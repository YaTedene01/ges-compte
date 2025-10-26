<?php

namespace App\Exceptions;

use Exception;

class CompteNotFoundException extends Exception
{
    public function __construct($compteId, $message = null, $code = 404, \Throwable $previous = null)
    {
        if (!$message) {
            $message = "Le compte avec l'ID {$compteId} n'existe pas";
        }
        parent::__construct($message, $code, $previous);
    }
}