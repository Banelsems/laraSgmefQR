<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\ConfigWriterInterface;
use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProviderInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Http\Filters\InvoiceFilters;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Banelsems\LaraSgmefQr\Services\PackageConfigurationService;
use Banelsems\LaraSgmefQr\Services\SgmefConnectionTester;
use Banelsems\LaraSgmefQr\Repositories\EloquentInvoiceRepository;
use Banelsems\LaraSgmefQr\Services\ReferenceDataProvider;
use Orchestra\Testbench\TestCase;

/**
 * Test de résolution du container : vérifie que tous les bindings
 * du ServiceProvider résolvent les bons types.
 */
class ContainerResolutionTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_resolves_sgmef_api_client(): void
    {
        $client = $this->app->make(SgmefApiClientInterface::class);

        $this->assertInstanceOf(
            \Banelsems\LaraSgmefQr\Services\SgmefApiClient::class,
            $client
        );
    }

    public function test_resolves_invoice_manager(): void
    {
        $manager = $this->app->make(InvoiceManagerInterface::class);

        $this->assertInstanceOf(InvoiceManager::class, $manager);
    }

    public function test_resolves_invoice_repository(): void
    {
        $repo = $this->app->make(InvoiceRepositoryInterface::class);

        $this->assertInstanceOf(EloquentInvoiceRepository::class, $repo);
    }

    public function test_resolves_ifu_resolver(): void
    {
        $resolver = $this->app->make(IfuResolverInterface::class);

        $this->assertInstanceOf(
            \Banelsems\LaraSgmefQr\Services\DefaultIfuResolver::class,
            $resolver
        );
    }

    public function test_resolves_reference_data_provider(): void
    {
        $provider = $this->app->make(ReferenceDataProviderInterface::class);

        $this->assertInstanceOf(ReferenceDataProvider::class, $provider);
    }

    public function test_resolves_config_writer(): void
    {
        $writer = $this->app->make(ConfigWriterInterface::class);

        $this->assertInstanceOf(
            \Banelsems\LaraSgmefQr\Infrastructure\FileConfigWriter::class,
            $writer
        );
    }

    public function test_resolves_api_client_factory(): void
    {
        $factory = $this->app->make(SgmefApiClientFactory::class);

        $this->assertInstanceOf(SgmefApiClientFactory::class, $factory);
    }

    public function test_resolves_connection_tester(): void
    {
        $tester = $this->app->make(SgmefConnectionTester::class);

        $this->assertInstanceOf(SgmefConnectionTester::class, $tester);
    }

    public function test_resolves_configuration_service(): void
    {
        $service = $this->app->make(PackageConfigurationService::class);

        $this->assertInstanceOf(PackageConfigurationService::class, $service);
    }

    public function test_resolves_invoice_filters(): void
    {
        $filters = $this->app->make(InvoiceFilters::class);

        $this->assertInstanceOf(InvoiceFilters::class, $filters);
    }

    public function test_singleton_api_client_is_same_instance(): void
    {
        $a = $this->app->make(SgmefApiClientInterface::class);
        $b = $this->app->make(SgmefApiClientInterface::class);

        $this->assertSame($a, $b);
    }

    public function test_factory_produces_isolated_instances(): void
    {
        $factory = $this->app->make(SgmefApiClientFactory::class);

        $config = new \Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig(
            'https://isolated.example.com',
            'isolated-token'
        );

        $client = $factory->make($config);

        $this->assertInstanceOf(SgmefApiClientInterface::class, $client);
        $this->assertNotSame($client, $this->app->make(SgmefApiClientInterface::class));
    }
}
