<?php

namespace App\Actions\API\V1\Club\SignPlayer;

use App\Exceptions\API\V1\PlayerAlreadyAssignedException;
use App\Models\Player;

final class EnsurePlayerIsNotAlreadyAssignedAction
{
    public function handle(Passable $passable, \Closure $next): Passable
    {
        $playerId = $passable->getPropertyFromData('player_id');
        $club = $passable->getPropertyFromData('club');

        $player = Player::findOrFail($playerId);

        if ($player->club_id) {
            if ($player->club_id === $club->id) {
                throw new PlayerAlreadyAssignedException(
                    "This Player is already assigned to this Club ({$club->name})."
                );
            }

            throw new PlayerAlreadyAssignedException(
                "This Player is already assigned to another Club ({$player->club->name})."
            );
        }

        $passable->setPlayer($player);

        return $next($passable);
    }
}
