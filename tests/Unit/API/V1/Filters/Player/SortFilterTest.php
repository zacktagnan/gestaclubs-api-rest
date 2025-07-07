<?php

namespace Tests\Unit\API\V1\Filters\Player;

use App\Filters\Player\SortFilter;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Pipeline\Pipeline;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:filters')]
#[Group('api:v1:unit:filters:player')]
#[Group('api:v1:unit:filters:player:sort')]
class SortFilterTest extends UnitTestCase
{
    protected array $ascFullNamePlayerArray;
    protected array $descFullNamePlayerArray;

    protected array $playersCreated;

    protected function setUp(): void
    {
        parent::setUp();

        // Clubs y jugadores asociados
        $clubZeta = Club::factory()->create(['name' => 'Zeta Club']);
        $clubAlpha = Club::factory()->create(['name' => 'Alpha Club']);
        $clubBeta = Club::factory()->create(['name' => 'Beta Club']);

        $this->playersCreated = [
            Player::factory()->create(['full_name' => 'Carlos', 'club_id' => $clubZeta->id]),
            Player::factory()->create(['full_name' => 'Andrés', 'club_id' => $clubAlpha->id]),
            Player::factory()->create(['full_name' => 'Beatriz', 'club_id' => $clubBeta->id]),
        ];

        $this->ascFullNamePlayerArray = ['Andrés', 'Beatriz', 'Carlos'];
        $this->descFullNamePlayerArray = ['Carlos', 'Beatriz', 'Andrés'];
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:full_name_asc')]
    public function it_sorts_by_full_name_ascending()
    {
        request()->merge([
            'sort_by' => 'full_name',
            'sort' => 'asc',
        ]);

        $players = app(Pipeline::class)
            ->send(Player::query())
            ->through([SortFilter::class])
            ->thenReturn()
            ->pluck('full_name')
            ->toArray();

        $this->assertSame(['Andrés', 'Beatriz', 'Carlos'], $players);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:club_name_desc')]
    public function it_sorts_by_club_name_descending()
    {
        request()->merge([
            'sort_by' => 'club_name',
            'sort' => 'desc',
        ]);

        $players = app(Pipeline::class)
            ->send(Player::query())
            ->through([SortFilter::class])
            ->thenReturn()
            ->with('club') // necesario para acceder a club.name si se quiere ver resultados
            ->get();

        $orderedClubNames = $players->pluck('club.name')->toArray();

        $this->assertSame(['Zeta Club', 'Beta Club', 'Alpha Club'], $orderedClubNames);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:invalid_sort_column')]
    // public function it_ignores_invalid_sort_column()
    // {
    //     request()->merge([
    //         'sort_by' => 'unknown_column',
    //         'sort' => 'asc',
    //     ]);

    //     $builder = Player::query();
    //     $result = app(Pipeline::class)
    //         ->send($builder)
    //         ->through([SortFilter::class])
    //         ->thenReturn();

    //     // La QUERY original debería permanecer sin orderBy aplicado
    //     $this->assertSame(
    //         $builder->toSql(),
    //         $result->toSql()
    //     );
    // }
    // o
    public function it_ignores_invalid_sort_column(): void
    {
        Player::factory()->create(['full_name' => 'Zeta']);
        Player::factory()->create(['full_name' => 'Alfa']);
        Player::factory()->create(['full_name' => 'Gamma']);

        request()->merge([
            'sort_by' => 'not_a_column',
            'sort' => 'desc',
        ]);

        $originalBuilder = Player::query();
        $pipelineBuilder = app(Pipeline::class)
            ->send($originalBuilder)
            ->through([SortFilter::class])
            ->thenReturn();
        $results = Player::filteredWithPipeline()->pluck('full_name')->all();

        // Como la columna no es válida, se aplica el orden por defecto del modelo (ID o sin orden específico)
        // Lo importante es que no falle y devuelva los datos sin aplicar ningún sort.
        $this->assertCount(6, $results); // 3 locales + 3 desde setUp
        $this->assertContains('Zeta', $results);
        $this->assertContains('Alfa', $results);
        $this->assertContains('Gamma', $results);
        $this->assertSame($originalBuilder->toSql(), $pipelineBuilder->toSql());
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:id_asc')]
    public function it_defaults_to_id_asc_if_nothing_is_passed()
    {
        request()->replace([]); // vaciar query

        $playersExpectedIds = collect($this->playersCreated)->pluck('id')->sort()->values()->all();

        $playersActualIds = app(Pipeline::class)
            ->send(Player::query())
            ->through([SortFilter::class])
            ->thenReturn()
            ->pluck('id')
            ->toArray();

        $this->assertEquals($playersExpectedIds, $playersActualIds);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:insensitive_direction')]
    public function it_allows_case_insensitive_sort_direction(): void
    {
        request()->merge([
            'sort_by' => 'full_name',
            'sort' => 'DESC', // mayúsculas
        ]);

        $results = Player::filteredWithPipeline()->pluck('full_name')->all();

        $this->assertSame($this->descFullNamePlayerArray, $results);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:sort:invalid_direction')]
    public function it_falls_back_to_ascending_if_direction_is_invalid(): void
    {
        request()->merge([
            'sort_by' => 'full_name',
            'sort' => 'invalid_direction', // dirección inválida
        ]);

        $results = Player::filteredWithPipeline()->pluck('full_name')->all();

        $this->assertSame($this->ascFullNamePlayerArray, $results, 'Orden ascendente esperado por fallback');
    }
}
