<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Contracts;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Orchestra\Testbench\TestCase;
use ReflectionClass;

/**
 * Tests de conformité des contrats (interfaces).
 *
 * Vérifie que les implémentations concrètes respectent exactement
 * les signatures déclarées dans les interfaces, afin de prévenir
 * les divergences de contrat détectées pendant l'audit v3.1.
 */
class ContractConformanceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_sgmef_api_client_implements_all_interface_methods(): void
    {
        $interface = new ReflectionClass(SgmefApiClientInterface::class);
        $implementation = new ReflectionClass(SgmefApiClient::class);

        foreach ($interface->getMethods() as $interfaceMethod) {
            $this->assertTrue(
                $implementation->hasMethod($interfaceMethod->getName()),
                'SgmefApiClient doit implémenter ::' . $interfaceMethod->getName()
            );
        }
    }

    public function test_invoice_manager_implements_all_interface_methods(): void
    {
        $interface = new ReflectionClass(InvoiceManagerInterface::class);
        $implementation = new ReflectionClass(InvoiceManager::class);

        foreach ($interface->getMethods() as $interfaceMethod) {
            $this->assertTrue(
                $implementation->hasMethod($interfaceMethod->getName()),
                'InvoiceManager doit implémenter ::' . $interfaceMethod->getName()
            );
        }
    }

    public function test_invoice_manager_interface_declares_sync_invoice(): void
    {
        $interface = new ReflectionClass(InvoiceManagerInterface::class);

        $this->assertTrue(
            $interface->hasMethod('syncInvoice'),
            'InvoiceManagerInterface doit déclarer syncInvoice() depuis v3.1.'
        );
    }

    public function test_sgmef_api_client_interface_get_status_returns_api_status_dto(): void
    {
        $interface = new ReflectionClass(SgmefApiClientInterface::class);
        $method = $interface->getMethod('getStatus');

        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType, 'getStatus() doit avoir un type de retour déclaré.');
        $this->assertEquals(
            'Banelsems\LaraSgmefQr\DTOs\ApiStatusDto',
            $returnType->getName(),
            'getStatus() doit retourner ApiStatusDto depuis v3.1 (et non mixed).'
        );
    }

    public function test_sgmef_api_client_get_status_returns_api_status_dto(): void
    {
        $implementation = new ReflectionClass(SgmefApiClient::class);
        $method = $implementation->getMethod('getStatus');

        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType, 'SgmefApiClient::getStatus() doit avoir un type de retour.');
        $this->assertEquals(
            'Banelsems\LaraSgmefQr\DTOs\ApiStatusDto',
            $returnType->getName()
        );
    }
}
