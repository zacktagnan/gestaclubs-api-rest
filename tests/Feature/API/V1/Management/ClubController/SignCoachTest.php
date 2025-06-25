<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use App\Models\Club;
use App\Models\Coach;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\ClubDataProvider;
use App\Notifications\Channels\EmailNotifier;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use PHPUnit\Framework\Attributes\DataProviderExternal;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:clubs')]
#[Group('api:v1:feat:management:clubs:sign_coach')]
class SignCoachTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    protected Coach $coachToSign;
    protected int $validSalaryToAssign;
    protected string $coachesTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coachToSign = Coach::factory()->create();
        $this->validSalaryToAssign = $this->club->budget - 1;
        $this->coachesTable = 'coaches';
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:success')]
    public function a_club_can_sign_a_coach(): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [
                'coach_id' => $this->coachToSign->id,
                'salary' => $this->validSalaryToAssign,
            ])
            ->assertCreated();

        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('message', 'Club has signed the Coach.');
        $response->assertJsonPath('data.id', $this->coachToSign->id);
        $response->assertJsonPath('data.salary', $this->validSalaryToAssign);
        $response->assertJsonPath('data.club.id', $this->club->id);

        $response->assertJsonFragment([
            'full_name' => $this->coachToSign->full_name,
            'email' => $this->coachToSign->email,
        ]);

        $this->assertDatabaseHas($this->coachesTable, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->club->id,
            'full_name' => $this->coachToSign['full_name'],
            'salary' => $this->validSalaryToAssign,
            'club_id' => $this->club->id,
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:malformed_request')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideInvalidClubSignCoachData')]
    public function coach_assignation_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:already_assigned')]
    public function coach_is_already_assigned_to_another_club(): void
    {
        $anotherClub = Club::factory()->create();
        $coachToSign = Coach::factory()->for($anotherClub)->create();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [
                'coach_id' => $coachToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:already_has_one')]
    public function club_already_has_one_coach(): void
    {
        Coach::factory()->for($this->club)->create();
        $coachToSign = Coach::factory()->create();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [
                'coach_id' => $coachToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:not_enough')]
    public function club_budget_is_not_enough_to_sign_coach(): void
    {
        $salaryToAssign = $this->club->budget + 1;

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [
                'coach_id' => $this->coachToSign->id,
                'salary' => $salaryToAssign,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $salaryToAssign = 1;

        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'signCoach', $this->club),
            method: 'postJson',
            payload: [],
            payloadGenerator: function (int $i) use ($salaryToAssign) {
                // Refrescar la relación para evitar usar datos cacheados
                $this->club->refresh();

                if ($this->club->coach) {
                    $this->club->coach->update([
                        'club_id' => null,
                        'salary' => null,
                    ]);
                }

                // Crear un nuevo coach para esta iteración
                $coach = Coach::factory()->create();

                return [
                    'coach_id' => $coach->id,
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
    #[Group('api:v1:feat:management:clubs:sign_coach:unauthenticated')]
    public function an_unauthenticated_user_cannot_sign_a_coach(): void
    {
        $this
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [])
            ->assertUnauthorized();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:sign_coach:notification_failure')]
    public function error_sending_notification_after_sign_a_coach(): void
    {
        // 1. Creado Coach y establecido un SALARY válido que asignar...

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
            ->postJson(route($this->clubsBaseRouteName . 'signCoach', $this->club), [
                'coach_id' => $this->coachToSign->id,
                'salary' => $this->validSalaryToAssign,
            ]);

        // 5. Verificar que se lanzó un error 500
        $response
            ->assertInternalServerError()
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment([
                'message' => 'Failed to send notification to assigned Coach: Fake notification failure.',
            ]);

        // 6. Verificar que no se asignó el club (rollback)
        $this->assertDatabaseMissing($this->coachesTable, [
            'id' => $this->coachToSign->id,
            'club_id' => $this->club->id,
        ]);
    }
}
