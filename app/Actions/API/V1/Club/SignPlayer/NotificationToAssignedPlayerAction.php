<?php

namespace App\Actions\API\V1\Club\SignPlayer;

use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\NotifierManager;
use App\Notifications\PlayerAssignedToClubNotification;

final class NotificationToAssignedPlayerAction
{
    public function __construct(
        private readonly NotifierManager $notifierManager
    ) {}

    public function handle(Passable $passable, \Closure $next): Passable
    {
        try {
            $player = $passable->getPlayer();
            $player->load('club');

            // -> SIN interfaz
            // $player->notify(new PlayerAssignedToClubNotification());
            // -> CON interfaz
            $this->notifierManager->notify($player, new PlayerAssignedToClubNotification());
        } catch (\Throwable $e) {
            throw new ErrorSendingNotificationException(
                'Failed to send notification to assigned Player: ' . $e->getMessage()
            );
        }

        return $next($passable);
    }
}
