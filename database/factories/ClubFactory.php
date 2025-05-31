<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Club>
 */
class ClubFactory extends Factory
{
    protected $model = Club::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $terms = [
            'Atlético',
            'Balompié',
            'Cultural',
            'Deportivo',
            'Estrella',
            'Independiente',
            'Internacional',
            'Olímpico',
            'Popular',
            'Racing',
            'Real',
            'Recreativo',
            'Sporting',
            'Unión',
        ];

        $term = fake()->randomElement($terms);

        $city = fake()->unique()->city();

        $name = fake()->randomElement([
            "$city $term",
            "$term $city",
            "$city $term FC",
            "$term FC $city",
            "$term $city FC",
            "$city $term Club",
            "$term Club $city",
            "$term $city Club",
        ]);

        return [
            'name' => $name,
            'budget' => fake()->numberBetween(7_400_000, 28_000_000),
        ];
    }
}
