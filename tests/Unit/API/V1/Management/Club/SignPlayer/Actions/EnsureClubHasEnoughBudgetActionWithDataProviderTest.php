<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer\Actions;

use App\Actions\API\V1\Club\SignPlayer\EnsureClubHasEnoughBudgetAction;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\Unit\SignPlayer\EnoughBudgetDataProvider;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:actions')]
#[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget')]
class EnsureClubHasEnoughBudgetActionWithDataProviderTest extends UnitTestCase
{
    protected EnsureClubHasEnoughBudgetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();

        $this->action = new EnsureClubHasEnoughBudgetAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget:success')]
    #[DataProviderExternal(EnoughBudgetDataProvider::class, 'provideSufficientBudgetScenarios')]
    public function it_allows_signing_when_club_has_enough_budget(
        int $budget,
        int $coachSalary,
        array $playerSalaries,
        int $playerSalary
    ): void {
        // Arrange
        $this->club->update(['budget' => $budget]);

        // Asignamos miembros al club para consumir el presupuesto
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: $coachSalary,
            playerSalaries: $playerSalaries
        );

        $passable = $this->setPassableForPlayerSigning(
            salary: $playerSalary,
        );

        // Act
        $result = $this->action->handle($passable, fn($p) => $p);

        // Assert
        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget:failure')]
    #[DataProviderExternal(EnoughBudgetDataProvider::class, 'provideExceededBudgetScenarios')]
    public function it_throws_exception_if_club_budget_is_exceeded(
        int $budget,
        int $coachSalary,
        array $playerSalaries,
        int $playerSalary
    ): void {
        $this->club->update(['budget' => $budget]);

        // Arrange: asignamos miembros al club para consumir el presupuesto
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: $coachSalary,
            playerSalaries: $playerSalaries,
        );

        // Fichando un nuevo Player, sobrepasando el BUDGET para que no sea suficiente
        $passable = $this->setPassableForPlayerSigning(
            salary: $playerSalary,
        );

        $this->expectExceptionAndDatabaseMissing(
            ClubBudgetExceededException::class,
            'Club has not enough budget for this Player signing.',
            $this->playersTable,
            [
                'id' => $this->player->id,
                'club_id' => $this->club->id,
            ],
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
