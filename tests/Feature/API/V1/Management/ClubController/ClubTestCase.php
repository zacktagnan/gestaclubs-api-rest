<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use Tests\TestCase;

abstract class ClubTestCase extends TestCase
{
    protected string $clubsBaseRouteName;
    protected string $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubsBaseRouteName = 'v1.clubs.';
        $this->table = 'clubs';
    }
}
