<?php

namespace Tests\Unit\API\V1\Models\Player\Scopes;

use App\Models\Club;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:models')]
#[Group('api:v1:unit:models:player')]
#[Group('api:v1:unit:models:player:scopes')]
#[Group('api:v1:unit:models:player:scopes:filtered_pipeline')]
class FilteredWithPipelineTest extends UnitTestCase
{
    #[Test]
    #[Group('api:v1:unit:models:player:scopes:filtered_pipeline:all_filters')]
    public function scope_filtered_with_pipeline_applies_all_filters(): void
    {
        $club = Club::factory()->create(['name' => 'Boca Juniors']);

        Player::factory()->create([
            'full_name' => 'Lucas Pérez',
            'email' => 'lucas@example.com',
            'salary' => 1_500_000,
            'club_id' => $club->id,
        ]);

        Player::factory()->create([
            'full_name' => 'Marcelo Díaz',
            'email' => 'marcelo@example.com',
            'salary' => 2_500_000,
            'club_id' => null,
        ]);

        request()->merge([
            'full_name' => 'Lucas',
            'email' => 'lucas@',
            'club_name' => 'Boca',
            'salary_min' => 1_000_000,
            'salary_max' => 2_000_000,
            'sort_by' => 'full_name',
            'sort' => 'asc',
        ]);

        $results = Player::filteredWithPipeline()->get();

        $this->assertCount(1, $results);
        $this->assertSame('Lucas Pérez', $results->first()->full_name);
    }

    #[Test]
    #[Group('api:v1:unit:models:player:scopes:filtered_pipeline:all_filters_desc')]
    public function scope_applies_all_filters_with_desc_sorting(): void
    {
        $club = Club::factory()->create(['name' => 'Boca Juniors']);

        Player::factory()->create([
            'full_name' => 'Lucas Pérez',
            'email' => 'lucas@example.com',
            'salary' => 1_500_000,
            'club_id' => $club->id,
        ]);

        Player::factory()->create([
            'full_name' => 'Alfredo González',
            'email' => 'alfredo@example.com',
            'salary' => 1_600_000,
            'club_id' => $club->id,
        ]);

        request()->merge([
            'full_name' => 'e',              // Ambas contienen "e"
            'email' => 'example.com',        // Ambas cumplen
            'club_name' => 'Boca',
            'salary_min' => 1_000_000,
            'salary_max' => 2_000_000,
            'sort_by' => 'full_name',
            'sort' => 'desc',
        ]);

        $results = Player::filteredWithPipeline()->pluck('full_name')->all();

        // Lucas debe ir primero porque ordenamos por full_name DESC
        $this->assertEquals(['Lucas Pérez', 'Alfredo González'], $results);
    }

    #[Test]
    #[Group('api:v1:unit:models:player:scopes:filtered_pipeline:no_filters')]
    public function scope_returns_all_players_when_no_filters_are_applied(): void
    {
        Player::factory()->create(['full_name' => 'Juan']);
        Player::factory()->create(['full_name' => 'Pedro']);
        Player::factory()->create(['full_name' => 'María']);

        // No pasamos ningún request()->merge()

        $results = Player::filteredWithPipeline()->pluck('full_name')->all();

        $this->assertCount(3, $results);
        $this->assertEqualsCanonicalizing(['Juan', 'Pedro', 'María'], $results);
    }
}
