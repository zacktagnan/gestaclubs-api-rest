<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer\Actions;

use App\Actions\API\V1\Club\SignPlayer\NotificationToAssignedPlayerAction;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Models\Player;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use App\Notifications\PlayerAssignedToClubNotification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:actions')]
#[Group('api:v1:unit:management:club:sign_player:actions:notification_to_assigned')]
class NotificationToAssignedPlayerActionTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;
    private NotificationToAssignedPlayerAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();

        // Asociamos el jugador al club
        $this->player->fill([
            'club_id' => $this->club->id,
            'salary' => $this->club->budget - 1,
        ])->save();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:notification_to_assigned:success')]
    public function it_sends_notification_to_assigned_player(): void
    {
        // Usamos el canal real pero sin sobrescribir nada

        // Si el canal de notificación fuera otro, como el de SMS, bastaría con:
        // -> sustituir 'mail' por 'sms'.
        // -> y usar el NOTIFIER correspondiente, en este caso el de SMSNotifier.

        // $emailNotifierInstance = new EmailNotifier();
        // $emailNotifierMock = Mockery::mock($emailNotifierInstance)->makePartial();
        // $emailNotifierMock
        //     ->shouldReceive('notify')
        //     ->once()
        //     ->with(
        //         Mockery::on(fn(Player $p) => $p->is($this->player)),
        //         Mockery::type(PlayerAssignedToClubNotification::class)
        //     );

        // $this->notifierManager = new NotifierManager([
        //     'mail' => $emailNotifierMock,
        // ]);

        // o ============================================================================

        $this->notifierManager = $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
            $mock->shouldReceive('notify')
                ->once()
                ->with(
                    Mockery::on(fn(Player $p) => $p->is($this->player)),
                    Mockery::type(PlayerAssignedToClubNotification::class)
                );
        });

        $this->action = new NotificationToAssignedPlayerAction($this->notifierManager);

        $passable = $this->setPassableForPlayerSigning(
            salary: $this->club->budget - 1,
        );
        // Simulando que el Pipeline ya ha inyectado el Player en el Passable
        $passable->setPlayer($this->player);

        $result = $this->action->handle($passable, fn($p) => $p);

        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:notification_to_assigned:failure')]
    public function it_throws_exception_if_notification_fails(): void
    {
        // Simulando un fallo en el canal mail
        // $emailNotifierInstance = new EmailNotifier();
        // $failingEmailNotifierMock = Mockery::mock($emailNotifierInstance)->makePartial();
        // $failingEmailNotifierMock
        //     ->shouldReceive('notify')
        //     ->once()
        //     ->andThrow(new \RuntimeException('Fake email notification failure'));

        // $this->notifierManager = new NotifierManager([
        //     'mail' => $failingEmailNotifierMock,
        // ]);

        // o ============================================================================

        $this->notifierManager = $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
            $mock->shouldReceive('notify')
                ->once()
                ->andThrow(new \RuntimeException('Fake email notification failure'));
        });

        $this->action = new NotificationToAssignedPlayerAction($this->notifierManager);

        $passable = $this->setPassableForPlayerSigning(
            salary: $this->club->budget - 1,
        );
        // Simulando que el Pipeline ya ha inyectado el Player en el Passable
        $passable->setPlayer($this->player);

        $this->expectExceptionOnly(
            ErrorSendingNotificationException::class,
            'Failed to send notification to assigned Player: Fake email notification failure',
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
