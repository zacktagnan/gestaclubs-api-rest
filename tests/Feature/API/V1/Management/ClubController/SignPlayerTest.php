<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use App\Models\Club;
use App\Models\Player;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\ClubDataProvider;
use App\Notifications\Channels\EmailNotifier;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use PHPUnit\Framework\Attributes\DataProviderExternal;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:clubs')]
#[Group('api:v1:management:clubs:sign_player')]
class SignPlayerTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    protected Player $playerToSign;
    protected int $validSalaryToAssign;
    protected string $playersTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerToSign = Player::factory()->create();
        $this->validSalaryToAssign = $this->club->budget - 1;
        $this->playersTable = 'players';
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:success')]
    public function a_club_can_sign_a_player(): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [
                'player_id' => $this->playerToSign->id,
                'salary' => $this->validSalaryToAssign,
            ])
            ->assertCreated();

        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('message', 'Club has signed the Player.');
        $response->assertJsonPath('data.id', $this->playerToSign->id);
        $response->assertJsonPath('data.salary', $this->validSalaryToAssign);
        $response->assertJsonPath('data.club.id', $this->club->id);

        $response->assertJsonFragment([
            'full_name' => $this->playerToSign->full_name,
            'email' => $this->playerToSign->email,
        ]);

        $this->assertDatabaseHas($this->playersTable, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->club->id,
            'full_name' => $this->playerToSign['full_name'],
            'salary' => $this->validSalaryToAssign,
            'club_id' => $this->club->id,
        ]);
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:malformed_request')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideInvalidClubSignPlayerData')]
    public function player_assignation_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:already_assigned')]
    public function player_is_already_assigned_to_another_club(): void
    {
        $anotherClub = Club::factory()->create();
        $playerToSign = Player::factory()->for($anotherClub)->create();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [
                'player_id' => $playerToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:already_belongs')]
    public function player_is_already_belongs_to_this_club(): void
    {
        $playerToSign = Player::factory()->for($this->club)->create();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [
                'player_id' => $playerToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:not_enough')]
    public function club_budget_is_not_enough_to_sign_player(): void
    {
        $salaryToAssign = $this->club->budget + 1;

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [
                'player_id' => $this->playerToSign->id,
                'salary' => $salaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $salaryToAssign = 1;

        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'signPlayer', $this->club),
            method: 'postJson',
            payload: [],
            payloadGenerator: function (int $i) use ($salaryToAssign) {
                $player = Player::factory()->create();

                return [
                    'player_id' => $player->id,
                    'salary' => $salaryToAssign + $i,
                ];
            },
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
            expectedStatus: 201
        ));
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:unauthenticated')]
    public function an_unauthenticated_user_cannot_sign_a_player(): void
    {
        $this
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [])
            ->assertUnauthorized();
    }

    #[Test]
    #[Group('api:v1:management:clubs:sign_player:notification_failure')]
    public function error_sending_notification_after_sign_a_player(): void
    {
        // 1. Creado(s) Player y establecido un SALARY válido que asignar...

        // 2. Para simular fallo en el envío de la notificación, se define un Mock
        // de una instancia del EmailNotifier
        // Debe ser de una instancia de EmailNotifier por ser una clase declarada como FINAL
        $emailNotifierInstance = new EmailNotifier();
        $emailNotifierMock = \Mockery::mock($emailNotifierInstance)->makePartial();
        $emailNotifierMock
            ->shouldReceive('notify')
            ->once()
            ->andThrow(new \RuntimeException('Fake notification failure.'));

        // 3. Inyectar instancia real de NotifierManager con el canal mockeado
        $this->app->instance(NotifierManager::class, new NotifierManager([
            'mail' => $emailNotifierMock,
        ]));

        // 4. Ejecutar petición
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signPlayer', $this->club), [
                'player_id' => $this->playerToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        // 5. Verificar que se lanzó un error 500
        $response
            ->assertInternalServerError()
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment([
                'message' => 'Failed to send notification to assigned Player: Fake notification failure.',
            ]);

        // 6. Verificar que no se asignó el club (rollback)
        $this->assertDatabaseMissing($this->playersTable, [
            'id' => $this->playerToSign->id,
            'club_id' => $this->club->id,
        ]);
    }
}
