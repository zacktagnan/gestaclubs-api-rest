<?php

namespace App\Exceptions\API\V1;

use Exception;

class ClubHasMembersException extends Exception
{
    public function __construct(string $message = 'This Club still has members assigned, so it cannot be deleted.')
    {
        parent::__construct($message);
    }
}
