<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use Tests\TestCase;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;

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

    protected function assignStaffToClub(int $coachSalary = 4_000_000, array $playerSalaries = []): void
    {
        Coach::factory()->for($this->club)->create([
            'salary' => $coachSalary,
        ]);

        foreach ($playerSalaries as $salary) {
            Player::factory()->for($this->club)->create([
                'salary' => $salary,
            ]);
        }
    }
}
