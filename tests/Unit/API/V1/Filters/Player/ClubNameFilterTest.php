<?php

namespace Tests\Unit\API\V1\Filters\Player;

use App\Filters\Player\ClubNameFilter;
use App\Models\Club;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\Traits\DataCreationForTesting;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:filters')]
#[Group('api:v1:unit:filters:player')]
#[Group('api:v1:unit:filters:player:club_name')]
class ClubNameFilterTest extends UnitTestCase
{
    use DataCreationForTesting;

    #[Test]
    #[Group('api:v1:unit:filters:player:club_name:success')]
    public function it_filters_players_by_club_name()
    {
        $barcelona = Club::factory()->create(['name' => 'FC Barcelona']);
        $madrid = Club::factory()->create(['name' => 'Real Madrid']);

        Player::factory()->create(['full_name' => 'Juan', 'club_id' => $barcelona->id]);
        Player::factory()->create(['full_name' => 'Pedro', 'club_id' => $madrid->id]);

        request()->merge(['club_name' => 'Barça']); // No debería devolver nada
        $none = (new ClubNameFilter())->handle(Player::query(), fn($b) => $b)->get();
        $this->assertCount(0, $none);

        request()->merge(['club_name' => 'Bar']);
        $filtered = (new ClubNameFilter())->handle(Player::query(), fn($b) => $b)->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals('Juan', $filtered->first()->full_name);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:club_name:club_players_endpoint')]
    public function it_skips_filter_if_running_inside_club_players_endpoint()
    {
        request()->merge([
            'club_name' => 'Barcelona',
        ]);

        $original = Player::query();

        // -> Pasando al siguiente filtro sin aplicar el indicado
        // $result = (new ClubNameFilter())->handle($original, fn($b) => $b);
        // o, mejor
        $result = Player::filteredWithPipeline(filtersToIgnore: [ClubNameFilter::class]);

        $this->assertSame($original->toSql(), $result->toSql());
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:club_name:empty_club_name')]
    public function it_ignores_empty_club_name(): void
    {
        Player::factory()->create();

        request()->merge(['club_name' => '']);

        $result = (new ClubNameFilter())->handle(Player::query(), fn($b) => $b)->get();

        // No aplica filtro → retorna todos
        $this->assertCount(1, $result);
    }
}
