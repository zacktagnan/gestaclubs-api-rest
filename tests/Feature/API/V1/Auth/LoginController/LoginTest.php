<?php

namespace Tests\Feature\API\V1\Auth\LoginController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\AuthDataProvider;
use Tests\Feature\API\V1\Auth\AuthTestCase;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use PHPUnit\Framework\Attributes\DataProviderExternal;

#[Group('api:v1')]
#[Group('api:v1:auth')]
#[Group('api:v1:auth:login')]
class LoginTest extends AuthTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:auth:login:success')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideUserBaseDataToLogin')]
    public function an_user_can_login_successfully(array $userBaseData): void
    {
        $userData = array_merge($userBaseData, [
            'email' => $this->user->email,
        ]);

        $response = $this
            ->postJson(route($this->authBaseRouteName . 'login'), $userData)
            ->assertOk();

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertArrayHasKey('token_type', $response->json('data'));
    }

    #[Test]
    #[Group('api:v1:auth:login:malformed_request')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideInvalidLoginData')]
    public function user_login_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this->postJson(route($this->authBaseRouteName . 'login'), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:auth:login:too_many_requests')]
    #[DataProviderExternal(AuthDataProvider::class, 'provideUserBaseDataToLogin')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $userBaseData): void
    {
        $userData = array_merge($userBaseData, [
            'email' => $this->user->email,
        ]);

        $this->assertRateLimitExceeded(
            route($this->authBaseRouteName . 'login'),
            'postJson',
            $userData,
            null,
            10,
        );
    }
}
