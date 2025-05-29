<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Traits\HasClub;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coach>
 */
class CoachFactory extends Factory
{
    use HasClub;

    protected $model = Coach::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'full_name' => fake()->name(),
            'full_name' => fake()->unique()->name(),
            'email' => fake()->unique()->safeEmail(),
            // 'salary' => rand(30_000, 100_000), // en vez de 30000 y 100000
            'salary' => fake()->numberBetween(30_000, 100_000),
            'club_id' => null,
        ];
    }
}
