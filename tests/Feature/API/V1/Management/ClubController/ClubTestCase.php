<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use Tests\TestCase;
use App\Models\Club;

abstract class ClubTestCase extends TestCase
{
    protected string $clubsBaseRouteName;
    protected string $table;
    protected Club $club;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubsBaseRouteName = 'v1.clubs.';
        $this->table = 'clubs';

        $this->club = Club::factory()->create();
    }
}
