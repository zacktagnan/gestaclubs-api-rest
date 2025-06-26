<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use Tests\TestCase;
use App\Models\Club;
use Tests\Helpers\Traits\DataCreationForTesting;

abstract class ClubTestCase extends TestCase
{
    use DataCreationForTesting;

    protected string $clubsBaseRouteName;
    protected string $table;
    protected Club $club;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubsBaseRouteName = 'v1.clubs.';
        $this->table = 'clubs';

        $this->club = $this->createClub();
    }
}
