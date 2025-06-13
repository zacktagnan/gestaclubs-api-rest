<?php

namespace App\Actions\API\V1\Coach\RemoveFromClub;

final class RemoveFromClubAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $coach = $passable->getCoach();
        $passable->setClub($coach->club);

        $coach->club_id = null;
        $coach->salary = null;

        $coach->save();
        $coach->unsetRelation('club');

        return $next($passable);
    }
}
