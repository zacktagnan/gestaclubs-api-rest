<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use Tests\TestCase;
use App\Models\Coach;

abstract class CoachTestCase extends TestCase
{
    protected string $coachesBaseRouteName;
    protected string $table;
    protected Coach $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coachesBaseRouteName = 'v1.coaches.';
        $this->table = 'coaches';

        $this->coach = Coach::factory()->create();
    }
}
