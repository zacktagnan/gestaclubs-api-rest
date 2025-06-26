<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use Tests\TestCase;
use App\Models\Coach;
use Tests\Helpers\Traits\DataCreationForTesting;

abstract class CoachTestCase extends TestCase
{
    use DataCreationForTesting;

    protected string $coachesBaseRouteName;
    protected string $table;
    protected Coach $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coachesBaseRouteName = 'v1.coaches.';
        $this->table = 'coaches';

        $this->coach = $this->createCoach();
    }
}
