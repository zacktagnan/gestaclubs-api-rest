<?php

namespace App\Actions\API\V1\Club\SignPlayer;

use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\PlayerAssignedToClubNotification;

final class NotificationToAssignedPlayerAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        try {
            $player = $passable->getPlayer();
            $player->load('club');
            $player->notify(new PlayerAssignedToClubNotification());
        } catch (\Throwable $e) {
            throw new ErrorSendingNotificationException(
                'Failed to send notification to assigned Player: ' . $e->getMessage()
            );
        }

        return $next($passable);
    }
}
