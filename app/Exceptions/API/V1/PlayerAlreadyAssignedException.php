<?php

namespace App\Exceptions\API\V1;

use Exception;

class PlayerAlreadyAssignedException extends Exception
{
    // protected $message = 'Player is already assigned to another Club.';
    // o
    public function __construct(string $message = 'Player is already assigned to another Club.')
    {
        parent::__construct($message);
    }
}
