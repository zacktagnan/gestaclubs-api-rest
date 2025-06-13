<?php

namespace App\Actions\API\V1\Player\RemoveFromClub;

use App\Models\Club;
use App\Models\Player;

final class Passable
{
    private Club $club;

    public function __construct(private readonly Player $player) {}

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getClub(): Club
    {
        return $this->club;
    }

    public function setClub(Club $club): void
    {
        $this->club = $club;
    }
}
