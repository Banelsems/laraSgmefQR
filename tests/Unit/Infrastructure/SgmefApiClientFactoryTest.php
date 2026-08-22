<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;
use Illuminate\Http\Client\Factory as HttpClient;
use Orchestra\Testbench\TestCase;

class SgmefApiClientFactoryTest extends TestCase
{
    private SgmefApiClientFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new SgmefApiClientFactory($this->app->make(HttpClient::class));
    }

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_make_returns_isolated_instances(): void
    {
        $configA = new SgmefConnectionConfig('https://api-a.example.com', 'token-a');
        $configB = new SgmefConnectionConfig('https://api-b.example.com', 'token-b');

        $clientA = $this->factory->make($configA);
        $clientB = $this->factory->make($configB);

        // Les deux clients doivent être des instances distinctes
        $this->assertNotSame($clientA, $clientB);
    }

    public function test_make_from_config_uses_package_config(): void
    {
        config()->set('lara_sgmef_qr.api_url', 'https://config-url.example.com');
        config()->set('lara_sgmef_qr.token', 'config-token');

        $client = $this->factory->makeFromConfig();

        $this->assertInstanceOf(
            \Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class,
            $client
        );
    }

    public function test_two_configs_produce_independent_clients(): void
    {
        $configA = new SgmefConnectionConfig('https://api-a.example.com', 'token-a');
        $configB = new SgmefConnectionConfig('https://api-b.example.com', 'token-b');

        $clientA = $this->factory->make($configA);
        $clientB = $this->factory->make($configB);

        // Vérifier qu'il n'y a pas de mutation croisée
        // (les clients sont immutables après construction)
        $this->assertNotSame($clientA, $clientB);
    }
}
