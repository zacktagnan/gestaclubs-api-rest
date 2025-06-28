<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach;

use Mockery;
use App\Models\Club;
use Tests\Unit\API\V1\UnitTestCase;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Actions\API\V1\Club\SignCoach\Pipeline as ClubSignCoachPipeline;
use App\Exceptions\API\V1\ClubAlreadyHasCoachException;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use App\Exceptions\API\V1\CoachAlreadyAssignedException;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\CoachAssignedToClubNotification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:pipeline')]
class PipelineTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
        $this->setUpCoach();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:pipeline:success')]
    public function it_signs_a_coach_successfully(): void
    {
        $this->app->instance(NotifierManager::class, $this->setNotifierManagerWithMockedNotificationChannel(
            'mail',
            new EmailNotifier(),
            function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->with(
                        Mockery::on(fn($notifiable) => $notifiable->is($this->coach)),
                        Mockery::type(CoachAssignedToClubNotification::class)
                    );
            }
        ));

        $salaryToAssign = $this->club->budget - 1;

        $result = ClubSignCoachPipeline::execute([
            'coach_id' => $this->coach->id,
            'salary' => $salaryToAssign,
            'club' => $this->club,
        ]);

        $signedCoach = $result->getCoach();

        $this->assertTrue($signedCoach->is($this->coach));
        $this->assertEquals($this->club->id, $signedCoach->club_id);
        $this->assertEquals($salaryToAssign, $signedCoach->salary);

        $this->assertDatabaseHas($this->coachesTable, [
            'id' => $signedCoach->id,
            'club_id' => $this->club->id,
            'salary' => $salaryToAssign,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:pipeline:already_has_one')]
    public function it_throws_exception_if_already_has_coach(): void
    {
        $coach = $this->createCoachAssignedToClub(
            club: $this->club,
            coachSalary: $this->club->budget - 1,
        );

        $this->expectException(ClubAlreadyHasCoachException::class);

        ClubSignCoachPipeline::execute([
            'coach_id' => $coach->id,
            'salary' => $this->club->budget - 1,
            'club' => $this->club,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:pipeline:already_assigned')]
    public function it_throws_exception_if_coach_is_already_assigned(): void
    {
        $otherClub = Club::factory()->create();
        $coach = $this->createCoachAssignedToClub(
            club: $otherClub,
            coachSalary: $otherClub->budget - 1,
        );

        $this->expectException(CoachAlreadyAssignedException::class);

        ClubSignCoachPipeline::execute([
            'coach_id' => $coach->id,
            'salary' => $this->club->budget - 1,
            'club' => $this->club,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:pipeline:not_enough')]
    public function it_throws_exception_if_club_has_not_enough_budget(): void
    {
        $this->expectException(ClubBudgetExceededException::class);

        ClubSignCoachPipeline::execute([
            'coach_id' => $this->coach->id,
            'salary' => $this->club->budget + 1,
            'club' => $this->club,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:pipeline:notification_failure')]
    public function it_throws_if_notification_fails(): void
    {
        $this->app->instance(NotifierManager::class, $this->setNotifierManagerWithMockedNotificationChannel(
            'mail',
            new EmailNotifier(),
            function ($mock) {
                $mock->shouldReceive('notify')
                    // ->once()
                    ->andThrow(new \RuntimeException('Fake notification failure.'));
            }
        ));

        $this->expectExceptionMessage('Failed to send notification to assigned Coach: Fake notification failure.');

        ClubSignCoachPipeline::execute([
            'coach_id' => $this->coach->id,
            'salary' => $this->club->budget - 1,
            'club' => $this->club,
        ]);
    }
}
