<?php

namespace Tests\Unit\API\V1;

use Tests\TestCase;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use Tests\Helpers\Traits\DataCreationForTesting;

abstract class UnitTestCase extends TestCase
{
    use DataCreationForTesting;

    protected Club $club;
    protected Coach $coach;
    protected Player $player;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function setUpClub(): void
    {
        $this->club = $this->createClub();
    }

    protected function setUpCoach(): void
    {
        $this->coach = $this->createCoach();
    }

    protected function setUpPlayer(): void
    {
        $this->player = $this->createPlayer();
    }
}
