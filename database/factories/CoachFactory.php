<?php

namespace Database\Factories;

use App\Models\Coach;
use Illuminate\Support\Str;
use App\Traits\API\V1\HasClub;
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
        $domains = [
            fake()->freeEmailDomain(),
            fake()->safeEmailDomain(),
            fake()->domainName(),
        ];
        $domain = '@' . fake()->randomElement($domains);

        $fullName = fake()->unique()->name();
        $username = Str::slug($fullName, '.');
        $email = $username . $domain;

        return [
            'full_name' => $fullName,
            'email' => $email,
        ];
    }
}
