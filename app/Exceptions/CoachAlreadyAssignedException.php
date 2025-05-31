<?php

namespace App\Exceptions;

use Exception;

class CoachAlreadyAssignedException extends Exception
{
    public function __construct(string $message = 'Coach is already assigned to another Club.')
    {
        parent::__construct($message);
    }
}
