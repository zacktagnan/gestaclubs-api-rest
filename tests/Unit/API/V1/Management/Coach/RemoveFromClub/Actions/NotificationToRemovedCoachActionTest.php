<?php

namespace Tests\Unit\API\V1\Management\Coach\RemoveFromClub\Actions;

use App\Actions\API\V1\Coach\RemoveFromClub\NotificationToRemovedCoachAction;
use App\Models\Coach;
use App\Notifications\CoachRemovedFromClubNotification;
use App\Notifications\NotifierManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\API\V1\Coach\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\Channels\EmailNotifier;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:coach')]
#[Group('api:v1:unit:management:coach:remove_from_club')]
#[Group('api:v1:unit:management:coach:remove_from_club:actions')]
#[Group('api:v1:unit:management:coach:remove_from_club:actions:notification_to_removed')]
class NotificationToRemovedCoachActionTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;
    private NotificationToRemovedCoachAction $action;
    private int $coachSalary = 5_000_000;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        $this->coach = DataWithRelationsHelper::assignCoachToClub(
            $this->club,
            coachSalary: $this->coachSalary,
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:coach:remove_from_club:actions:notification_to_removed:success')]
    public function it_sends_notification_to_removed_coach(): void
    {
        $this->notifierManager = $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
            $mock->shouldReceive('notify')
                ->once()
                ->with(
                    Mockery::on(fn(Coach $c) => $c->is($this->coach)),
                    Mockery::type(CoachRemovedFromClubNotification::class)
                );
        });

        $this->action = new NotificationToRemovedCoachAction($this->notifierManager);

        $passable = new RemoveFromClubPassable($this->coach);
        $passable->setClub($this->club);

        $result = $this->action->handle($passable, fn($p) => $p);

        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:coach:remove_from_club:actions:notification_to_removed:failure')]
    public function it_throws_exception_if_notification_fails(): void
    {
        $this->action = new NotificationToRemovedCoachAction(
            $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->andThrow(new \RuntimeException('Fake email notification failure'));
            })
        );

        $passable = new RemoveFromClubPassable($this->coach);
        $passable->setClub($this->club);

        $this->expectExceptionOnly(
            ErrorSendingNotificationException::class,
            'Failed to send notification to removed Coach: Fake email notification failure',
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
