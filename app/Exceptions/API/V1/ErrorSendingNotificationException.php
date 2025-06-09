<?php

namespace App\Exceptions\API\V1;

use Exception;

class ErrorSendingNotificationException extends Exception
{
    public function __construct(string $message = 'The sending notification failed.')
    {
        parent::__construct($message);
    }
}
