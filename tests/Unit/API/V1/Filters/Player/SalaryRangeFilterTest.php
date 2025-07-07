<?php

namespace Tests\Unit\API\V1\Filters\Player;

use App\Filters\Player\SalaryRangeFilter;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\Traits\DataCreationForTesting;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:filters')]
#[Group('api:v1:unit:filters:player')]
#[Group('api:v1:unit:filters:player:salary_range')]
class SalaryRangeFilterTest extends UnitTestCase
{
    use DataCreationForTesting;

    #[Test]
    #[Group('api:v1:unit:filters:player:salary_range:success')]
    public function it_filters_players_by_salary_range()
    {
        $this->createPlayersWithVariousData([
            ['salary' => 1_000_000],
            ['salary' => 1_500_000],
            ['salary' => 2_000_000],
        ]);
        // o
        // Player::factory()->create(['salary' => 1_000_000]);
        // Player::factory()->create(['salary' => 1_500_000]);
        // Player::factory()->create(['salary' => 2_000_000]);

        request()->merge([
            // 'salary_min' => '1200000',
            // 'salary_max' => '1800000',
            // o
            'salary_min' => 1_200_000,
            'salary_max' => 1_800_000,
            // pero NO así
            // 'salary_min' => '1_200_000',
            // 'salary_max' => '1_800_000',
        ]);

        $result = (new SalaryRangeFilter())->handle(
            Player::query(),
            fn($builder) => $builder
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1_500_000, $result->first()->salary);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:salary_range:min_only')]
    public function it_filters_by_salary_min_only()
    {
        Player::factory()->create(['salary' => 800_000]);
        Player::factory()->create(['salary' => 1_200_000]);

        request()->merge(['salary_min' => 1_000_000]);
        // pero, en este caso, así no
        // request()->merge(['salary_min' => '1_000_000']);

        $result = (new SalaryRangeFilter())->handle(
            Player::query(),
            fn($builder) => $builder
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1_200_000, $result->first()->salary);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:salary_range:max_only')]
    public function it_filters_by_salary_max_only()
    {
        Player::factory()->create(['salary' => 800_000]);
        Player::factory()->create(['salary' => 1_200_000]);

        request()->merge(['salary_max' => 1_000_000]);
        // pero, en este caso, así no
        // request()->merge(['salary_max' => '1_000_000']);

        $result = (new SalaryRangeFilter())->handle(
            Player::query(),
            fn($builder) => $builder
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals(800_000, $result->first()->salary);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:salary_range:non_numeric_values')]
    public function it_ignores_non_numeric_salary_values(): void
    {
        Player::factory()->create(['salary' => 1_000_000]);

        request()->merge([
            'salary_min' => 'abc',
            'salary_max' => 'xyz',
        ]);

        $result = (new SalaryRangeFilter())->handle(Player::query(), fn($b) => $b)->get();

        // Se espera que no aplique ningún filtro (se devuelvan todos)
        $this->assertCount(1, $result);
    }
}
