<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use Tests\TestCase;
use App\Models\Player;
use Tests\Helpers\Traits\DataCreationForTesting;

abstract class PlayerTestCase extends TestCase
{
    use DataCreationForTesting;

    protected string $playersBaseRouteName;
    protected string $table;
    protected Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playersBaseRouteName = 'v1.players.';
        $this->table = 'players';

        $this->player = $this->createPlayer();
    }
}
