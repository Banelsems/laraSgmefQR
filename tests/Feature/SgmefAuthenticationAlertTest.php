<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Events\SgmefAuthenticationAlert;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class SgmefAuthenticationAlertTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_401_dispatches_authentication_alert_event(): void
    {
        Event::fake([SgmefAuthenticationAlert::class]);

        Http::fake(['api.example.com/*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $factory = new SgmefApiClientFactory($this->app->make(HttpClient::class));
        $client = $factory->make(new SgmefConnectionConfig(
            'https://api.example.com',
            'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign'
        ));

        try {
            $client->getStatus();
        } catch (\Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException) {
            // Expected
        }

        Event::assertDispatched(SgmefAuthenticationAlert::class, function ($event) {
            return $event->httpStatus === 401
                && $event->endpoint === '/info/status';
        });
    }

    public function test_403_dispatches_authentication_alert_event(): void
    {
        Event::fake([SgmefAuthenticationAlert::class]);

        Http::fake(['api.example.com/*' => Http::response(['message' => 'Forbidden'], 403)]);

        $factory = new SgmefApiClientFactory($this->app->make(HttpClient::class));
        $client = $factory->make(new SgmefConnectionConfig(
            'https://api.example.com',
            'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign'
        ));

        try {
            $client->getStatus();
        } catch (\Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException) {
            // Expected
        }

        Event::assertDispatched(SgmefAuthenticationAlert::class, function ($event) {
            return $event->httpStatus === 403;
        });
    }

    public function test_500_does_not_dispatch_authentication_alert(): void
    {
        Event::fake([SgmefAuthenticationAlert::class]);

        Http::fake(['api.example.com/*' => Http::response(['message' => 'Server error'], 500)]);

        $factory = new SgmefApiClientFactory($this->app->make(HttpClient::class));
        $client = $factory->make(new SgmefConnectionConfig(
            'https://api.example.com',
            'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign'
        ));

        try {
            $client->getStatus();
        } catch (\Banelsems\LaraSgmefQr\Exceptions\SgmefServerException) {
            // Expected
        }

        Event::assertNotDispatched(SgmefAuthenticationAlert::class);
    }
}
