<?php

namespace App\Actions\API\V1\Player\RemoveFromClub;

final class RemoveFromClubAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $player = $passable->getPlayer();
        $passable->setClub($player->club);

        $player->club_id = null;
        $player->salary = null;

        $player->save();
        $player->unsetRelation('club');

        return $next($passable);
    }
}
