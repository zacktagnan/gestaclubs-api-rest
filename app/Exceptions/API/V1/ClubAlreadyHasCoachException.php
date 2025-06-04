<?php

namespace App\Exceptions\API\V1;

use Exception;

class ClubAlreadyHasCoachException extends Exception
{
    public function __construct(string $message = 'This Club already has a Coach assigned.')
    {
        parent::__construct($message);
    }
}
