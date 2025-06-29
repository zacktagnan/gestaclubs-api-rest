<?php

namespace Tests\Unit\API\V1\Services;

use App\Services\API\V1\ApiResponseService;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:services')]
#[Group('api:v1:unit:services:api_response_service')]
class ApiResponseServiceTest extends UnitTestCase
{
    #[Test]
    #[Group('api:v1:unit:services:api_response_service:success_response')]
    public function it_returns_a_success_json_response()
    {
        $response = ApiResponseService::success(['foo' => 'bar'], 'Everything OK', Response::HTTP_OK);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Everything OK', $data['message']);
        $this->assertEquals(['foo' => 'bar'], $data['data']);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:error_response')]
    public function it_returns_an_error_response()
    {
        $response = ApiResponseService::error();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Error', $data['message']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:unauthorized_response')]
    public function it_returns_an_unauthorized_response()
    {
        $response = ApiResponseService::unauthorized();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Unauthorized', $data['message']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:forbidden_response')]
    public function it_returns_a_forbidden_response()
    {
        $response = ApiResponseService::forbidden();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Forbidden', $data['message']);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:not_found_response')]
    public function it_returns_a_not_found_response()
    {
        $response = ApiResponseService::notFound();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Not Found', $data['message']);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:internal_server_error_response')]
    public function it_returns_an_internal_server_error_response()
    {
        $response = ApiResponseService::internalServerError();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Internal Server Error', $data['message']);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:throttled_response')]
    public function it_returns_a_throttled_response()
    {
        $maxAttempts = 60;
        $retryAfter = 60;
        $response = ApiResponseService::throttled($maxAttempts, $retryAfter);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals("Too many attempts. Please try again in {$retryAfter} seconds.", $data['message']);
        $this->assertEquals($maxAttempts, $data['max_attempts']);
        $this->assertEquals($retryAfter, $data['retry_after']);
        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    #[Test]
    #[Group('api:v1:unit:services:api_response_service:unprocessable_entity_response')]
    public function it_returns_an_unprocessable_entity_response()
    {
        $response = ApiResponseService::unprocessableEntity();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Unprocessable Entity', $data['message']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
