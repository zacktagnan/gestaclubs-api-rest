<?php

namespace App\Exceptions\API\V1;

use App\Contracts\DomainUnprocessableException;
use Exception;

class ClubAlreadyHasCoachException extends Exception implements DomainUnprocessableException
{
    public function __construct(string $message = 'This Club already has a Coach assigned.')
    {
        parent::__construct($message);
    }
}
