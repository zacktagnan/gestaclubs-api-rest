<?php

namespace Tests\Unit\API\V1\Models\Club;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:models')]
#[Group('api:v1:unit:models:club')]
#[Group('api:v1:unit:models:club:get_invested_budget')]
class GetInvestedBudgetTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
    }

    #[Test]
    #[Group('api:v1:unit:models:club:get_invested_budget:success')]
    public function it_calculates_the_total_invested_budget_successfully(): void
    {
        $this->club->update(['budget' => 20_000_000]);

        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: 6_000_000,
            playerSalaries: [3_000_000, 2_000_000]
        );

        $this->assertSame(11_000_000, $this->club->getInvestedBudget());
    }

    #[Test]
    #[Group('api:v1:unit:models:club:get_invested_budget:returns_zero')]
    public function it_returns_zero_when_there_are_no_players_or_coach(): void
    {
        $this->assertSame(0, $this->club->getInvestedBudget());
    }
}
