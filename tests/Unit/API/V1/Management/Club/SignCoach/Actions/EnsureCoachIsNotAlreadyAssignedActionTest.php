<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\EnsureCoachIsNotAlreadyAssignedAction;
use App\Actions\API\V1\Club\SignCoach\Passable as ClubSignCoachPassable;
use App\Exceptions\API\V1\CoachAlreadyAssignedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:ensure_not_assigned')]
class EnsureCoachIsNotAlreadyAssignedActionTest extends UnitTestCase
{
    protected EnsureCoachIsNotAlreadyAssignedAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        $this->action = new EnsureCoachIsNotAlreadyAssignedAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_not_assigned:success')]
    public function it_allows_coach_without_club(): void
    {
        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );

        $next = fn(ClubSignCoachPassable $p) => $p;

        $result = $this->action->handle($passable, $next);

        $this->assertTrue($result->getCoach()->is($this->coach));
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_not_assigned:already_in_same')]
    public function it_throws_if_coach_already_belongs_to_the_same_club(): void
    {
        // Asignamos manualmente al mismo club
        $this->coach->club()->associate($this->club)->save();

        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );

        $this->expectExceptionOnly(
            CoachAlreadyAssignedException::class,
            "This Coach is already assigned to this Club ({$this->club->name}).",
            fn() => $this->action->handle($passable, fn() => null)
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_not_assigned:already_in_another')]
    public function it_throws_if_coach_belongs_to_another_club(): void
    {
        $anotherClub = $this->createClub();

        $this->coach->club()->associate($anotherClub)->save();

        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );

        $this->expectExceptionAndDatabaseMissing(
            CoachAlreadyAssignedException::class,
            "This Coach is already assigned to another Club ({$anotherClub->name}).",
            $this->coachesTable,
            [
                'id' => $this->coach->id,
                'club_id' => $this->club->id,
            ],
            fn() => $this->action->handle($passable, fn() => null),
        );
    }
}
