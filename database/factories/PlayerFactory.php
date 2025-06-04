<?php

namespace Database\Factories;

use App\Models\Player;
use App\Traits\API\V1\HasClub;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    use HasClub;

    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->unique()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
