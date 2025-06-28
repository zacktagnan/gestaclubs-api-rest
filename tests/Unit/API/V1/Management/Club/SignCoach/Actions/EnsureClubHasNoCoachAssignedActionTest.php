<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\EnsureClubHasNoCoachAssignedAction;
use App\Actions\API\V1\Club\SignCoach\Passable as ClubSignCoachPassable;
use App\Exceptions\API\V1\ClubAlreadyHasCoachException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:ensure_has_no_coach')]
class EnsureClubHasNoCoachAssignedActionTest extends UnitTestCase
{
    protected EnsureClubHasNoCoachAssignedAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        $this->action = new EnsureClubHasNoCoachAssignedAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_has_no_coach:success')]
    public function it_allows_if_club_has_no_coach_assigned(): void
    {
        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );

        $next = fn(ClubSignCoachPassable $p) => $p;

        $result = $this->action->handle($passable, $next);

        // Verificando que se retorna el mismo objeto (y el flujo continúa sin excepción)
        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_has_no_coach:failure')]
    public function it_throws_if_club_already_has_coach_assigned(): void
    {
        // Asignamos manualmente al mismo club
        $this->coach->club()->associate($this->club)->save();

        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );

        $this->expectExceptionOnly(
            ClubAlreadyHasCoachException::class,
            "This Club already has a Coach assigned ({$this->club->coach->full_name}).",
            fn() => $this->action->handle($passable, fn() => null)
        );
    }
}
