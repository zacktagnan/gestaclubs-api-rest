<?php

namespace Tests\Feature\API\V1\Auth\RegisterController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\AuthDataProvider;
use Tests\Feature\API\V1\Auth\AuthTestCase;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use PHPUnit\Framework\Attributes\DataProviderExternal;

#[Group('api:v1')]
#[Group('api:v1:auth')]
#[Group('api:v1:auth:register')]
class RegisterTest extends AuthTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:auth:register:success')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideUserDataToRegister')]
    public function an_user_can_register_successfully(array $userData): void
    {
        $response = $this
            ->postJson(route($this->authBaseRouteName . 'register'), $userData)
            ->assertOk();

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertArrayHasKey('token_type', $response->json('data'));
    }

    // #[Test]
    // public function user_registration_fails_with_missing_required_fields(): void
    // {
    //     $response = $this->postJson(route($this->authBaseRouteName . 'register'), []);

    //     $response
    //         ->assertStatus(422)
    //         ->assertJson([
    //             'message' => 'The name field is required. (and 3 more errors)',
    //         ])
    //         ->assertJsonStructure([
    //             'errors' => [
    //                 'name',
    //                 'email',
    //                 'password',
    //                 'device_name',
    //             ],
    //         ]);
    // }
    // o, con algo más detallado en cuanto a los errores específicos de validación...
    #[Test]
    #[Group('api:v1:auth:register:malformed_request')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideInvalidRegistrationData')]
    public function user_registration_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this->postJson(route($this->authBaseRouteName . 'register'), $invalidData);

        $response
            //->assertStatus(422)
            // o
            //->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            // o
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:auth:register:too_many_requests')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideUserDataToRegister')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $userData): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->authBaseRouteName . 'register'),
            method: 'postJson',
            payload: $userData,
            uniqueFields: 'email', // Campo único para variar en cada petición
            maxAttempts: 10 // Máximo de intentos permitidos
        ));
    }
}
