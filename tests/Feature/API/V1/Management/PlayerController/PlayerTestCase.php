<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use Tests\TestCase;
use App\Models\Player;

abstract class PlayerTestCase extends TestCase
{
    protected string $playersBaseRouteName;
    protected string $table;
    protected Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playersBaseRouteName = 'v1.players.';
        $this->table = 'players';

        $this->player = Player::factory()->create();
    }
}
