<?php

namespace Tests\Unit\API\V1\Management\Coach\RemoveFromClub;

use App\Notifications\Channels\EmailNotifier;
use Tests\Unit\API\V1\UnitTestCase;
use App\Notifications\NotifierManager;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Actions\API\V1\Coach\RemoveFromClub\Pipeline as RemoveFromClubPipeline;
use App\Actions\API\V1\Coach\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\CoachRemovedFromClubNotification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\Helpers\DataWithRelationsHelper;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:coach')]
#[Group('api:v1:unit:management:coach:remove_from_club')]
#[Group('api:v1:unit:management:coach:remove_from_club:pipeline')]
class PipelineTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;
    private int $coachSalary = 5_000_000;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
        $this->setUpCoach();

        $this->coach = DataWithRelationsHelper::assignCoachToClub(
            $this->club,
            coachSalary: $this->coachSalary,
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:coach:remove_from_club:pipeline:success')]
    public function it_removes_a_coach_successfully(): void
    {
        $this->app->instance(NotifierManager::class, $this->setNotifierManagerWithMockedNotificationChannel(
            'mail',
            new EmailNotifier(),
            function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->with(
                        Mockery::on(fn($notifiable) => $notifiable->is($this->coach)),
                        Mockery::type(CoachRemovedFromClubNotification::class)
                    );
            }
        ));

        $result = RemoveFromClubPipeline::execute($this->coach);

        $this->assertInstanceOf(RemoveFromClubPassable::class, $result);

        $this->assertTrue($result->getClub()->is($this->club));
        $this->assertTrue($result->getCoach()->is($this->coach));

        $this->assertNull($result->getCoach()->club_id);
        $this->assertNull($result->getCoach()->salary);

        $this->assertFalse($result->getCoach()->relationLoaded('club'));

        $this->assertDatabaseHas($this->coachesTable, [
            'id' => $this->coach->id,
            'club_id' => null,
            'salary' => null,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:coach:remove_from_club:pipeline:notification_failure')]
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

        // $this->expectException(ErrorSendingNotificationException::class);
        // $this->expectExceptionMessage('Failed to send notification to removed Coach: Fake notification failure.');

        // $this->assertDatabaseHas($this->coachesTable, [
        //     'id' => $this->coach->id,
        //     'club_id' => $this->club->id,
        //     'salary' => $this->coachSalary,
        // ]);

        // RemoveFromClubPipeline::execute($this->coach);

        // o ============================================================================

        $this->expectExceptionAndAssertDatabase(
            ErrorSendingNotificationException::class,
            'Failed to send notification to removed Coach: Fake notification failure.',
            fn() => $this->assertDatabaseHas($this->coachesTable, [
                'id' => $this->coach->id,
                'club_id' => $this->club->id,
                'salary' => $this->coachSalary,
            ]),
            fn() => RemoveFromClubPipeline::execute($this->coach)
        );
    }
}
