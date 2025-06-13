<?php

namespace App\Actions\API\V1\Club\SignCoach;

use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\CoachAssignedToClubNotification;
use App\Notifications\NotifierManager;

final class NotificationToAssignedCoachAction
{
    public function __construct(
        private readonly NotifierManager $notifierManager
    ) {}

    public function handle(Passable $passable, \Closure $next): Passable
    {
        try {
            $coach = $passable->getCoach();
            $coach->load('club');

            // -> SIN interfaz
            // $coach->notify(new CoachAssignedToClubNotification());
            // -> CON interfaz
            $this->notifierManager->notify($coach, new CoachAssignedToClubNotification());
        } catch (\Throwable $e) {
            throw new ErrorSendingNotificationException(
                'Failed to send notification to assigned Coach: ' . $e->getMessage()
            );
        }

        return $next($passable);
    }
}
