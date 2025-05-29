<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Coach;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = Club::all();
        $minRangeSalary = 700_000;
        $maxRangeSalary = 1_400_000;

        foreach ($clubs as $club) {
            $used = $club->players()->sum('salary');
            $budget = $club->budget;

            $maxCoachSalary = $budget - $used;

            if ($maxCoachSalary < $minRangeSalary) {
                continue;
            }

            $salary = fake()->numberBetween($minRangeSalary, min($maxRangeSalary, $maxCoachSalary));

            Coach::factory()
                ->withClub($club)
                ->create([
                    'salary' => $salary,
                ]);
        }

        Coach::factory()
            ->count(11)
            ->withoutClub()
            ->create();
    }
}
