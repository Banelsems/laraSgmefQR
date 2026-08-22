<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit;

use Orchestra\Testbench\TestCase;

/**
 * Vérifie que les classes publiques du package sont autoloadées
 * via Composer depuis leurs namespaces canoniques après le Lot 2.
 */
class AutoloadStructureTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_canonical_command_classes_are_autoloadable(): void
    {
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Console\Commands\EmecefGenerateDeclaration::class));
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Console\Commands\CheckConfigCommand::class));
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Console\Commands\GenerateStatExportCommand::class));
    }

    public function test_legacy_command_class_is_autoloadable(): void
    {
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Commands\EmecefGenerateDeclaration::class));
    }

    public function test_core_service_classes_are_autoloadable(): void
    {
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Services\InvoiceManager::class));
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Services\SgmefApiClient::class));
        $this->assertTrue(class_exists(\Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService::class));
    }

    public function test_contract_classes_are_autoloadable(): void
    {
        $this->assertTrue(interface_exists(\Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class));
        $this->assertTrue(interface_exists(\Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface::class));
    }

    public function test_src_tests_directory_no_longer_exists(): void
    {
        $this->assertFalse(
            is_dir(__DIR__ . '/../../src/Tests'),
            'src/Tests/ ne doit plus exister après l\'unification des tests vers tests/.'
        );
    }
}
