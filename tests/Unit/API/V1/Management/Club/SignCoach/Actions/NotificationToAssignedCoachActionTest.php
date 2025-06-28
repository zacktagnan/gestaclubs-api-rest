<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\NotificationToAssignedCoachAction;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Models\Coach;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use App\Notifications\CoachAssignedToClubNotification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:notification_to_assigned')]
class NotificationToAssignedCoachActionTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;
    private NotificationToAssignedCoachAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        // Asociamos el Coach al Club
        $this->coach->fill([
            'club_id' => $this->club->id,
            'salary' => $this->club->budget - 1,
        ])->save();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:notification_to_assigned:success')]
    public function it_sends_notification_to_assigned_coach(): void
    {
        // Usamos el canal real pero sin sobrescribir nada
        $this->notifierManager = $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
            $mock->shouldReceive('notify')
                ->once()
                ->with(
                    Mockery::on(fn(Coach $c) => $c->is($this->coach)),
                    Mockery::type(CoachAssignedToClubNotification::class)
                );
        });

        $this->action = new NotificationToAssignedCoachAction($this->notifierManager);

        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );
        // Simulando que el Pipeline ya ha inyectado el Coach en el Passable
        $passable->setCoach($this->coach);

        $result = $this->action->handle($passable, fn($p) => $p);

        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:notification_to_assigned:failure')]
    public function it_throws_exception_if_notification_fails(): void
    {
        // Simulando un fallo en el canal mail
        $this->notifierManager = $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
            $mock->shouldReceive('notify')
                ->once()
                ->andThrow(new \RuntimeException('Fake email notification failure'));
        });

        $this->action = new NotificationToAssignedCoachAction($this->notifierManager);

        $passable = $this->setPassableForCoachSigning(
            salary: $this->club->budget - 1,
        );
        // Simulando que el Pipeline ya ha inyectado el Coach en el Passable
        $passable->setCoach($this->coach);

        $this->expectExceptionOnly(
            ErrorSendingNotificationException::class,
            'Failed to send notification to assigned Coach: Fake email notification failure',
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
