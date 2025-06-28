<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\EnsureClubHasEnoughBudgetAction;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\Unit\SignCoach\EnoughBudgetDataProvider;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget_with_data_provider')]
class EnsureClubHasEnoughBudgetActionWithDataProviderTest extends UnitTestCase
{
    protected EnsureClubHasEnoughBudgetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        $this->action = new EnsureClubHasEnoughBudgetAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget_with_data_provider:success')]
    #[DataProviderExternal(EnoughBudgetDataProvider::class, 'provideSufficientBudgetScenarios')]
    public function it_allows_signing_when_club_has_enough_budget(
        int $budget,
        array $playerSalaries,
        int $coachSalary
    ): void {
        // Arrange
        $this->club->update(['budget' => $budget]);

        DataWithRelationsHelper::assignPlayersStaffToClub(
            $this->club,
            $playerSalaries
        );

        $passable = $this->setPassableForCoachSigning(
            salary: $coachSalary,
        );

        // Act
        $result = $this->action->handle($passable, fn($p) => $p);

        // Assert
        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget_with_data_provider:failure')]
    #[DataProviderExternal(EnoughBudgetDataProvider::class, 'provideExceededBudgetScenarios')]
    public function it_throws_exception_if_club_budget_is_exceeded(
        int $budget,
        array $playerSalaries,
        int $coachSalary
    ): void {
        // Arrange
        $this->club->update(['budget' => $budget]);

        // Asignamos miembros al club para consumir el presupuesto
        DataWithRelationsHelper::assignPlayersStaffToClub(
            $this->club,
            playerSalaries: $playerSalaries
        );

        $passable = $this->setPassableForCoachSigning(
            salary: $coachSalary,
        );

        $this->expectExceptionAndAssertDatabase(
            ClubBudgetExceededException::class,
            'Club has not enough budget for this Coach signing.',
            fn() => $this->assertDatabaseMissing($this->coachesTable, [
                'id' => $this->coach->id,
                'club_id' => $this->club->id,
            ]),
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
