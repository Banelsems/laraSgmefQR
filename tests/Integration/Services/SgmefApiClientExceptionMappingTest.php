<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Integration\Services;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNotFoundException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefRateLimitException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefServerException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefValidationException;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class SgmefApiClientExceptionMappingTest extends TestCase
{
    private SgmefApiClientInterface $client;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = new SgmefApiClientFactory($this->app->make(HttpClient::class));
        $this->client = $factory->make(new SgmefConnectionConfig(
            'https://api.example.com',
            'test-token'
        ));
    }

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_401_maps_to_authentication_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Unauthorized'], 401)]);

        try {
            $this->client->getStatus();
            $this->fail('Expected SgmefAuthenticationException');
        } catch (SgmefAuthenticationException $e) {
            $this->assertSame(401, $e->httpStatus());
            $this->assertInstanceOf(SgmefApiException::class, $e);
        }
    }

    public function test_403_maps_to_authentication_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Forbidden'], 403)]);

        $this->expectException(SgmefAuthenticationException::class);
        $this->client->getStatus();
    }

    public function test_400_maps_to_validation_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Bad request'], 400)]);

        try {
            $this->client->getStatus();
            $this->fail('Expected SgmefValidationException');
        } catch (SgmefValidationException $e) {
            $this->assertSame(400, $e->httpStatus());
        }
    }

    public function test_422_maps_to_validation_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Validation failed'], 422)]);

        $this->expectException(SgmefValidationException::class);
        $this->client->getStatus();
    }

    public function test_404_maps_to_not_found_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Not found'], 404)]);

        $this->expectException(SgmefNotFoundException::class);
        $this->client->getStatus();
    }

    public function test_429_maps_to_rate_limit_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(
            ['message' => 'Too many requests'],
            429,
            ['Retry-After' => '15']
        )]);

        try {
            $this->client->getStatus();
            $this->fail('Expected SgmefRateLimitException');
        } catch (SgmefRateLimitException $e) {
            $this->assertSame(429, $e->httpStatus());
            $this->assertSame(15, $e->retryAfter());
        }
    }

    public function test_500_maps_to_server_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Internal error'], 500)]);

        $this->expectException(SgmefServerException::class);
        $this->client->getStatus();
    }

    public function test_503_maps_to_server_exception(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'Service unavailable'], 503)]);

        $this->expectException(SgmefServerException::class);
        $this->client->getStatus();
    }

    public function test_all_exceptions_catchable_via_base(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['message' => 'error'], 500)]);

        try {
            $this->client->getStatus();
        } catch (SgmefApiException $e) {
            $this->assertInstanceOf(SgmefServerException::class, $e);
        }
    }
}
