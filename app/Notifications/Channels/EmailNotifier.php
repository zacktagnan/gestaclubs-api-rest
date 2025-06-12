<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Notifications\Contracts\NotifierInterface;
use App\Models\Contracts\NotifiableEntityInterface;

final class EmailNotifier implements NotifierInterface
{
    public function notify(NotifiableEntityInterface $notifiable, Notification $notification): void
    {
        /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Notifications\Notifiable $notifiable */
        $notifiable->notify($notification);
    }
}
