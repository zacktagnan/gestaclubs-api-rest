<?php

namespace App\Notifications;

use App\Models\Contracts\NotifiableEntityInterface;
use App\Notifications\Contracts\NotifierInterface;
use Illuminate\Notifications\Notification;

final class NotifierManager
{
    /**
     * @param array<string, NotifierInterface> $notifiers
     */
    public function __construct(private readonly array $notifiers) {}

    public function notify(NotifiableEntityInterface $notifiable, Notification $notification): void
    {
        $channels = $notifiable->preferredNotificationChannels();

        if (empty($channels)) {
            $channels = [
                'mail', // Default channel if none specified
            ];
        }

        foreach ($channels as $channel) {
            if (isset($this->notifiers[$channel])) {
                $this->notifiers[$channel]->notify($notifiable, $notification);
            }
        }
    }
}
