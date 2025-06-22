<?php

namespace App\Exceptions\API\V1;

use App\Contracts\DomainUnprocessableException;
use Exception;

class ClubBudgetExceededException extends Exception implements DomainUnprocessableException
{
    public function __construct(string $message = 'Club has not enough budget for this signing.')
    {
        parent::__construct($message);
    }
}
