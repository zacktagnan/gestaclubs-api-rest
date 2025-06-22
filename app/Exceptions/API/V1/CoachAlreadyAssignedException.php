<?php

namespace App\Exceptions\API\V1;

use App\Contracts\DomainUnprocessableException;
use Exception;

class CoachAlreadyAssignedException extends Exception implements DomainUnprocessableException
{
    public function __construct(string $message = 'Coach is already assigned to another Club.')
    {
        parent::__construct($message);
    }
}
