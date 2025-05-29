<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $clubBudgetIncrementSeeder = new ClubBudgetIncrementSeeder();

        $this->call([
            UserSeeder::class,

            ClubSeeder::class,
            PlayerSeeder::class,
        ]);

        $clubBudgetIncrementSeeder->minIncrement = 500_000;
        $clubBudgetIncrementSeeder->maxIncrement = 700_000;
        $clubBudgetIncrementSeeder->run();

        $this->call(CoachSeeder::class);

        $clubBudgetIncrementSeeder->minIncrement = 3_000_000;
        $clubBudgetIncrementSeeder->maxIncrement = 5_000_000;
        $clubBudgetIncrementSeeder->run();
    }
}
