<?php

namespace App\Actions\API\V1\Club\SignPlayer;

use App\Models\Player;

final class Passable
{
    private Player $player;

    public function __construct(private readonly array $data) {}

    public function getPropertyFromData(string $property): mixed
    {
        return data_get($this->data, $property);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }
}
