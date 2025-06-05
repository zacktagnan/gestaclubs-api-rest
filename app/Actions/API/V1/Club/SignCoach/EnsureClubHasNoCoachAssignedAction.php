<?php

namespace App\Actions\API\V1\Club\SignCoach;

use App\Exceptions\API\V1\ClubAlreadyHasCoachException;

final class EnsureClubHasNoCoachAssignedAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $club = $passable->getPropertyFromData('club');

        if ($club->coach) {
            throw new ClubAlreadyHasCoachException("This Club already has a Coach assigned ({$club->coach->full_name}).");
        }

        return $next($passable);
    }
}
