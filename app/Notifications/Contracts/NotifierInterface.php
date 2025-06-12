<?php

namespace App\Notifications\Contracts;

use App\Models\Contracts\NotifiableEntityInterface;
use Illuminate\Notifications\Notification;

interface NotifierInterface
{
    public function notify(NotifiableEntityInterface $notifiable, Notification $notification): void;
}
