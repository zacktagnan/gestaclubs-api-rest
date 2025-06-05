<?php

namespace App\Actions\API\V1\Club\SignCoach;

use App\Exceptions\API\V1\CoachAlreadyAssignedException;
use App\Models\Coach;

final class EnsureCoachIsNotAlreadyAssignedAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $coachId = $passable->getPropertyFromData('coach_id');
        $club = $passable->getPropertyFromData('club');

        $coach = Coach::findOrFail($coachId);

        if ($coach->club_id) {
            if ($coach->club_id === $club->id) {
                throw new CoachAlreadyAssignedException(
                    "This Coach is already assigned to this Club ({$club->name})."
                );
            }

            throw new CoachAlreadyAssignedException(
                "This Coach is already assigned to another Club ({$coach->club->name})."
            );
        }

        $passable->setCoach($coach);

        return $next($passable);
    }
}
