<?php

namespace Tests\Feature\API\V1\Filters\Player;

use App\Filters\Player\ClubNameFilter;
use App\Filters\Player\EmailFilter;
use App\Filters\Player\FullNameFilter;
use App\Filters\Player\SalaryRangeFilter;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Pipeline\Pipeline;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:filters')]
#[Group('api:v1:feat:filters:player')]
#[Group('api:v1:feat:filters:player:combined_filters')]
class CombinedFiltersTest extends TestCase
{
    #[Test]
    #[Group('api:v1:feat:filters:player:combined_filters:success')]
    public function test_it_applies_multiple_filters_together(): void
    {
        $club = Club::factory()->create(['name' => 'Real Madrid']);
        $otherClub = Club::factory()->create(['name' => 'Barcelona']);

        Player::factory()->create([
            'full_name' => 'Carlos Pérez',
            'email' => 'carlos@example.com',
            'salary' => 1_200_000,
            'club_id' => $club->id,
        ]);

        Player::factory()->create([
            'full_name' => 'Pedro Martínez',
            'email' => 'pedro@example.com',
            'salary' => 1_800_000,
            'club_id' => $club->id,
        ]);

        Player::factory()->create([
            'full_name' => 'Carlos García',
            'email' => 'carlos.garcia@other.com',
            'salary' => 900_000,
            'club_id' => $otherClub->id,
        ]);

        // Simular los filtros combinados
        request()->merge([
            'full_name' => 'Carlos',
            'email' => 'carlos@',
            'club_name' => 'Real Madrid',
            'salary_min' => 1_000_000,
            'salary_max' => 1_500_000,
        ]);

        $result = app(Pipeline::class)
            ->send(Player::query())
            ->through([
                FullNameFilter::class,
                EmailFilter::class,
                ClubNameFilter::class,
                SalaryRangeFilter::class,
            ])
            ->thenReturn()
            ->get();

        $this->assertCount(1, $result);
        $this->assertSame('Carlos Pérez', $result->first()->full_name);
    }
}
