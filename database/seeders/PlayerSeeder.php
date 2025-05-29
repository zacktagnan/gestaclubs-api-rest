<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = Club::all();
        $minRangeSalary = 400_000;
        $maxRangeSalary = 1_100_000;

        foreach ($clubs as $club) {
            $budget = $club->budget;
            $used = 0;

            while (true) {
                $salary = fake()->numberBetween($minRangeSalary, $maxRangeSalary);

                if (($used + $salary) > $budget) {
                    break;
                }

                Player::factory()
                    ->withClub($club)
                    ->create([
                        'salary' => $salary,
                    ]);

                $used += $salary;
            }
        }

        Player::factory()
            ->count(69)
            ->withoutClub()
            ->create();
    }
}
