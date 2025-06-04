<?php

namespace App\Actions\API\V1\Club\SignPlayer;

final class AssignPlayerToClubAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $player = $passable->getPlayer();
        $playerSalary = $passable->getPropertyFromData('salary');
        $club = $passable->getPropertyFromData('club');

        $player->club_id = $club->id;
        $player->salary = $playerSalary;
        $player->save();

        return $next($passable);
    }
}
