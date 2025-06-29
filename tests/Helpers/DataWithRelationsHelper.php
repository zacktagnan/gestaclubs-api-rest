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

    public static function assignPlayersStaffToClub(Club $club, array $playerSalaries = []): void
    {
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

    // public static function createClubWithRelations(
    //     int $clubBudget = 21_000_000,
    //     bool $withCoach = true,
    //     int $coachSalary = 5_000_000,
    //     // int $playerCount = 4,
    //     // int $playerSalary = 7_000_000
    // )
    // public static function createClubWithRelations(
    //     int $clubBudget = 21_000_000,
    //     bool $withCoach = true,
    //     int $coachSalary = 5_000_000,
    //     // int $playerCount = 4,
    //     // int $playerSalary = 7_000_000
    // ): array {
    //     $club = Club::factory()
    //         ->state(['budget' => $clubBudget])
    //         ->create();

    //     $coach = null;
    //     if ($withCoach) {
    //         $coach = Coach::factory()->for($club)->create([
    //             'salary' => $coachSalary,
    //         ]);
    //     }

    //     // Por si se deseara crear Player(s) también
    //     // $players = Player::factory()->count($playerCount)->for($club)->create([
    //     //     'salary' => $playerSalary,
    //     // ]);

    //     // return compact('club', 'coach');
    //     // return compact('club', 'coach', 'players');
    //     return [
    //         'club' => $club,
    //         'coach' => $coach,
    //         // 'players' => $players,
    //     ];
    // }
    public static function createClubWithRelations(int $totalPlayers = 4, bool $withPlayer = false): array
    {
        $club = Club::factory()
            ->hasPlayers($totalPlayers)->create();

        $coach = Coach::factory()->for($club)->create();

        $player = null;
        if ($withPlayer) {
            $player = Player::factory()->for($club)->create();
        }

        return [
            'coach' => $coach,
            'player' => $player,
        ];
    }
}
