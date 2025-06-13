<?php

namespace App\Actions\API\V1\Player\RemoveFromClub;

use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\NotifierManager;
use App\Notifications\PlayerRemovedFromClubNotification;

final class NotificationToRemovedPlayerAction
{
    public function __construct(
        private readonly NotifierManager $notifierManager
    ) {}

    public function handle(Passable $passable, \Closure $next): Passable
    {
        try {
            $player = $passable->getPlayer();
            $club = $passable->getClub();

            // -> SIN interfaz
            // $player->notify(new PlayerRemovedFromClubNotification($club));
            // -> CON interfaz
            $this->notifierManager->notify($player, new PlayerRemovedFromClubNotification($club));
        } catch (\Throwable $e) {
            throw new ErrorSendingNotificationException(
                'Failed to send notification to removed Player: ' . $e->getMessage()
            );
        }

        return $next($passable);
    }
}
