<?php

namespace Tests\Helpers;

use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;

class DataWithRelationsHelper
{
    public static function assignStaffToClub(Club $club, int $coachSalary = 4_000_000, array $playerSalaries = []): void
    {
        // $club ??= $this->createClub();
        Coach::factory()->for($club)->create([
            'salary' => $coachSalary,
        ]);

        foreach ($playerSalaries as $salary) {
            Player::factory()->for($club)->create([
                'salary' => $salary,
            ]);
        }
    }

    public static function assignCoachToClub(Club $club, int $coachSalary = 4_000_000): Coach
    {
        return Coach::factory()->for($club)->create([
            'salary' => $coachSalary,
        ]);
    }

    public static function assignPlayerToClub(Club $club, int $playerSalary = 7_000_000): Player
    {
        return Player::factory()->for($club)->create([
            'salary' => $playerSalary,
        ]);
    }
}
