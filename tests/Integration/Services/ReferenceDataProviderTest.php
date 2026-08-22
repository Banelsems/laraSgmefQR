<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Integration\Services;

use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProviderInterface;
use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProvenance;
use Banelsems\LaraSgmefQr\Services\ReferenceDataProvider;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Orchestra\Testbench\TestCase;

class ReferenceDataProviderTest extends TestCase
{
    private $apiClient;
    private ReferenceDataProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = Mockery::mock(\Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class);
        $this->app->instance(\Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class, $this->apiClient);

        $this->provider = new ReferenceDataProvider(
            $this->apiClient,
            3600,
            'test_sgmef',
            []
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_first_call_uses_api(): void
    {
        Cache::flush();

        $this->apiClient
            ->shouldReceive('getTaxGroups')
            ->once()
            ->andReturn([
                \Banelsems\LaraSgmefQr\DTOs\TaxGroupDto::from(['code' => 'B', 'name' => 'B', 'rate' => 18.0]),
            ]);

        $result = $this->provider->getTaxGroups();

        $this->assertCount(1, $result);
        $this->assertSame(ReferenceDataProvenance::API, $this->provider->lastProvenance());
    }

    public function test_second_call_uses_cache(): void
    {
        Cache::flush();

        $this->apiClient
            ->shouldReceive('getTaxGroups')
            ->once()
            ->andReturn([
                \Banelsems\LaraSgmefQr\DTOs\TaxGroupDto::from(['code' => 'B', 'name' => 'B', 'rate' => 18.0]),
            ]);

        $this->provider->getTaxGroups();
        // Second call should use cache, API not called again
        $result = $this->provider->getTaxGroups();

        $this->assertCount(1, $result);
        $this->assertSame(ReferenceDataProvenance::CACHE, $this->provider->lastProvenance());
    }

    public function test_api_failure_uses_fallback(): void
    {
        Cache::flush();

        $this->apiClient
            ->shouldReceive('getTaxGroups')
            ->once()
            ->andThrow(new \Banelsems\LaraSgmefQr\Exceptions\SgmefApiException('API down'));

        $provider = new ReferenceDataProvider(
            $this->apiClient,
            3600,
            'test_sgmef',
            [
                'tax_groups' => ['A' => 0, 'B' => 18],
            ]
        );

        $result = $provider->getTaxGroups();

        $this->assertNotEmpty($result);
        $this->assertSame(ReferenceDataProvenance::FALLBACK, $provider->lastProvenance());
    }

    public function test_api_failure_without_fallback_throws(): void
    {
        Cache::flush();

        $this->apiClient
            ->shouldReceive('getPaymentTypes')
            ->once()
            ->andThrow(new \Banelsems\LaraSgmefQr\Exceptions\SgmefApiException('API down'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Impossible de charger le référentiel');

        $this->provider->getPaymentTypes();
    }

    public function test_api_down_with_valid_cache_uses_cache(): void
    {
        Cache::flush();

        // Prime the cache
        $this->apiClient
            ->shouldReceive('getInvoiceTypes')
            ->once()
            ->andReturn([
                \Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto::from(['type' => 'FV', 'description' => 'FV']),
            ]);

        $this->provider->getInvoiceTypes();

        // Now API fails, but cache should be used
        $this->apiClient
            ->shouldReceive('getInvoiceTypes')
            ->andThrow(new \Banelsems\LaraSgmefQr\Exceptions\SgmefApiException('API down'));

        $result = $this->provider->getInvoiceTypes();

        $this->assertNotEmpty($result);
        $this->assertSame(ReferenceDataProvenance::CACHE, $this->provider->lastProvenance());
    }
}
