<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Mockery;

/**
 * Tests unitaires pour InvoiceManager
 */
class InvoiceManagerTest extends TestCase
{
    use RefreshDatabase;

    private $apiClient;
    private $invoiceManager;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\Spatie\LaravelData\Data::class)) {
            $this->markTestSkipped('spatie/laravel-data non installé dans cet environnement');
        }
        
        $this->apiClient = Mockery::mock(SgmefApiClientInterface::class);
        $this->invoiceManager = new InvoiceManager($this->apiClient);
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

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Test de création de facture réussie
     */
    public function test_can_create_invoice_successfully(): void
    {
        $invoiceData = $this->createValidInvoiceRequestDto();
        
        $apiResponse = new InvoiceResponseDto(
            'test-uid-123',
            1000,
            180,
            null,
            null,
            'pending'
        );

        $this->apiClient
            ->shouldReceive('createInvoice')
            ->with($invoiceData)
            ->andReturn($apiResponse);

        $invoice = $this->invoiceManager->createInvoice($invoiceData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('test-uid-123', $invoice->uid);
        $this->assertEquals('1234567890123', $invoice->ifu);
        $this->assertEquals(InvoiceStatusEnum::PENDING, $invoice->status);
        $this->assertEquals(1000, $invoice->total_amount);
        $this->assertNotNull($invoice->raw_request);
        $this->assertNotNull($invoice->raw_response);
    }

    /**
     * Test de création de facture avec erreur API
     */
    public function test_create_invoice_handles_api_error(): void
    {
        $invoiceData = $this->createValidInvoiceRequestDto();
        
        $this->apiClient
            ->shouldReceive('createInvoice')
            ->with($invoiceData)
            ->andThrow(new SgmefApiException('API Error', 400));

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Erreur lors de la création de la facture: API Error');

        $this->invoiceManager->createInvoice($invoiceData);
    }

    /**
     * Test de création de facture avec données invalides
     */
    public function test_create_invoice_fails_with_invalid_data(): void
    {
        $client = new ClientDto(null, ''); // Nom vide (invalide)
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        $invoiceData = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Données de facture invalides');

        $this->invoiceManager->createInvoice($invoiceData);
    }

    /**
     * Test de confirmation de facture réussie
     */
    public function test_can_confirm_invoice_successfully(): void
    {
        // Créer une facture en attente
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::PENDING,
        ]);

        $securityElements = new SecurityElementsDto(
            '2023-07-02T15:22:34+00:00',
            'QR123456789',
            'MECF123456',
            'CTR123',
            'NIM123456',
            'OK'
        );

        $this->apiClient
            ->shouldReceive('confirmInvoice')
            ->with('test-uid-123')
            ->andReturn($securityElements);

        $confirmedInvoice = $this->invoiceManager->confirmInvoice('test-uid-123');

        $this->assertEquals(InvoiceStatusEnum::CONFIRMED, $confirmedInvoice->status);
        $this->assertEquals('QR123456789', $confirmedInvoice->qr_code_data);
        $this->assertEquals('MECF123456', $confirmedInvoice->mecf_code);
        $this->assertNotNull($confirmedInvoice->confirmed_at);
    }

    /**
     * Test de confirmation de facture avec erreur dans la réponse
     */
    public function test_confirm_invoice_handles_security_error(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::PENDING,
        ]);

        $securityElements = new SecurityElementsDto(
            '2023-07-02T15:22:34+00:00',
            'QR123456789',
            'MECF123456',
            'CTR123',
            'NIM123456',
            'ERROR',
            'Erreur de sécurité'
        );

        $this->apiClient
            ->shouldReceive('confirmInvoice')
            ->with('test-uid-123')
            ->andReturn($securityElements);

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Erreur lors de la confirmation: ERROR: Erreur de sécurité');

        $this->invoiceManager->confirmInvoice('test-uid-123');
    }

    /**
     * Test de confirmation de facture avec statut invalide
     */
    public function test_confirm_invoice_fails_with_invalid_status(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::CONFIRMED, // Déjà confirmée
        ]);

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('La facture test-uid-123 ne peut pas être confirmée');

        $this->invoiceManager->confirmInvoice('test-uid-123');
    }

    /**
     * Test d'annulation de facture
     */
    public function test_can_cancel_invoice_successfully(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::PENDING,
        ]);

        $this->apiClient
            ->shouldReceive('cancelInvoice')
            ->with('test-uid-123')
            ->andReturn(['status' => 'cancelled']);

        $cancelledInvoice = $this->invoiceManager->cancelInvoice('test-uid-123');

        $this->assertEquals(InvoiceStatusEnum::CANCELLED, $cancelledInvoice->status);
        $this->assertNotNull($cancelledInvoice->cancelled_at);
    }

    /**
     * Test de récupération de facture par UID
     */
    public function test_can_get_invoice_by_uid(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
        ]);

        $retrievedInvoice = $this->invoiceManager->getInvoice('test-uid-123');

        $this->assertEquals($invoice->id, $retrievedInvoice->id);
        $this->assertEquals('test-uid-123', $retrievedInvoice->uid);
    }

    /**
     * Test de récupération de facture inexistante
     */
    public function test_get_invoice_throws_exception_when_not_found(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Facture introuvable avec l\'UID: non-existent-uid');

        $this->invoiceManager->getInvoice('non-existent-uid');
    }

    /**
     * Test de synchronisation de facture
     */
    public function test_can_sync_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
        ]);

        $apiResponse = new InvoiceResponseDto(
            'test-uid-123',
            1000,
            180,
            null,
            null,
            'confirmed'
        );

        $this->apiClient
            ->shouldReceive('getInvoice')
            ->with('test-uid-123')
            ->andReturn($apiResponse);

        $syncedInvoice = $this->invoiceManager->syncInvoice('test-uid-123');

        $this->assertEquals($invoice->id, $syncedInvoice->id);
        $this->assertArrayHasKey('sync', $syncedInvoice->raw_response);
    }

    /**
     * Crée un DTO de requête de facture valide pour les tests
     */
    private function createValidInvoiceRequestDto(): InvoiceRequestDto
    {
        $client = new ClientDto(null, 'John Doe', '+229 12345678', '123 Rue Test');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        return new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );
    }
}
