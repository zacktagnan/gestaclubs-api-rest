<?php

namespace Tests\Helpers\Traits;

use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use Tests\Helpers\DataWithRelationsHelper;

trait DataCreationForTesting
{
    protected function createClub(): Club
    {
        return Club::factory()->create();
    }

    protected function createClubWithStaff(int $coachSalary = 4_000_000, array $playerSalaries = []): Club
    {
        $club = $this->createClub();
        DataWithRelationsHelper::assignStaffToClub($club, $coachSalary, $playerSalaries);

        return $club;
    }

    protected function createCoach(): Coach
    {
        return Coach::factory()->create();
    }

    protected function createPlayer(): Player
    {
        return Player::factory()->create();
    }

    protected function createCoachAssignedToClub(Club $club, int $coachSalary = 4_000_000): Coach
    {
        return DataWithRelationsHelper::assignCoachToClub($club, $coachSalary);
    }

    protected function createPlayerAssignedToClub(Club $club, int $playerSalary = 7_000_000): Player
    {
        return DataWithRelationsHelper::assignPlayerToClub($club, $playerSalary);
    }
}
