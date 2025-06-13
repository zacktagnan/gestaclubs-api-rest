<?php

namespace App\Actions\API\V1\Coach\RemoveFromClub;

use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\NotifierManager;
use App\Notifications\CoachRemovedFromClubNotification;

final class NotificationToRemovedCoachAction
{
    public function __construct(
        private readonly NotifierManager $notifierManager
    ) {}

    public function handle(Passable $passable, \Closure $next): Passable
    {
        try {
            $coach = $passable->getCoach();
            $club = $passable->getClub();

            // -> SIN interfaz
            // $player->notify(new CoachRemovedFromClubNotification($club));
            // -> CON interfaz
            $this->notifierManager->notify($coach, new CoachRemovedFromClubNotification($club));
        } catch (\Throwable $e) {
            throw new ErrorSendingNotificationException(
                'Failed to send notification to removed Coach: ' . $e->getMessage()
            );
        }

        return $next($passable);
    }
}
