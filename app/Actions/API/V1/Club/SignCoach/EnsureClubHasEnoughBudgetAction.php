<?php

namespace App\Actions\API\V1\Club\SignCoach;

use App\Exceptions\API\V1\ClubBudgetExceededException;

final class EnsureClubHasEnoughBudgetAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $coachSalary = $passable->getPropertyFromData('salary');
        $club = $passable->getPropertyFromData('club');

        $usedBudget = $club->players()->sum('salary');
        if (($usedBudget + $coachSalary) > $club->budget) {
            throw new ClubBudgetExceededException('Club has not enough budget for this Coach signing.');
        }

        return $next($passable);
    }
}
