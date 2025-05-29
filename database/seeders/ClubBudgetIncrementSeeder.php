<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;

class ClubBudgetIncrementSeeder extends Seeder
{
    public int $minIncrement = 4_000_000;
    public int $maxIncrement = 7_000_000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Club::all()->each(function ($club) {
            $club->increment('budget', rand($this->minIncrement, $this->maxIncrement));
        });
    }
}
