<?php

namespace App\Actions\API\V1\Club\SignCoach;

final class AssignCoachToClubAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $coach = $passable->getCoach();
        $coachSalary = $passable->getPropertyFromData('salary');
        $club = $passable->getPropertyFromData('club');

        $coach->club_id = $club->id;
        $coach->salary = $coachSalary;
        $coach->save();

        return $next($passable);
    }
}
