<?php

namespace App\Exceptions\API\V1;

use Exception;

class CoachAlreadyAssignedException extends Exception
{
    public function __construct(string $message = 'Coach is already assigned to another Club.')
    {
        parent::__construct($message);
    }
}
