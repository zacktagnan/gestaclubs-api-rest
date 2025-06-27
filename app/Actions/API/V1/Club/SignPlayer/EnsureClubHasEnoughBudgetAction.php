<?php

namespace App\Actions\API\V1\Club\SignPlayer;

use App\Exceptions\API\V1\ClubBudgetExceededException;

final class EnsureClubHasEnoughBudgetAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $playerSalary = $passable->getPropertyFromData('salary');
        $club = $passable->getPropertyFromData('club');

        $usedBudget = $club->getInvestedBudget();
        if (($usedBudget + $playerSalary) > $club->budget) {
            throw new ClubBudgetExceededException('Club has not enough budget for this Player signing.');
        }

        return $next($passable);
    }
}
