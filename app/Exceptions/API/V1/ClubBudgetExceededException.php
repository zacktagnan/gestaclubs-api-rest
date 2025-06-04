<?php

namespace App\Exceptions\API\V1;

use Exception;

class ClubBudgetExceededException extends Exception
{
    public function __construct(string $message = 'Club has not enough budget for this signing.')
    {
        parent::__construct($message);
    }
}
