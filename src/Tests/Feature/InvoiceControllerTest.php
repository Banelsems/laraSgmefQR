<?php

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Mockery;

/**
 * Tests fonctionnels pour InvoiceController
 */
class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private $invoiceManager;
    private $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->invoiceManager = Mockery::mock(InvoiceManagerInterface::class);
        $this->apiClient = Mockery::mock(SgmefApiClientInterface::class);
        
        $this->app->instance(InvoiceManagerInterface::class, $this->invoiceManager);
        $this->app->instance(SgmefApiClientInterface::class, $this->apiClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test d'affichage de la liste des factures
     */
    public function test_can_view_invoices_index(): void
    {
        Invoice::factory()->count(5)->create();

        $response = $this->get(route('sgmef.invoices.index'));

        $response->assertStatus(200);
        $response->assertViewIs('lara-sgmef-qr::invoices.index');
        $response->assertViewHas('invoices');
    }

    /**
     * Test de filtrage des factures par statut
     */
    public function test_can_filter_invoices_by_status(): void
    {
        Invoice::factory()->create(['status' => InvoiceStatusEnum::PENDING]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::CONFIRMED]);

        $response = $this->get(route('sgmef.invoices.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    /**
     * Test d'affichage du formulaire de création
     */
    public function test_can_view_create_form(): void
    {
        $this->apiClient
            ->shouldReceive('getTaxGroups')
            ->andReturn([['code' => 'A', 'name' => 'Exonéré']]);
            
        $this->apiClient
            ->shouldReceive('getPaymentTypes')
            ->andReturn([['code' => 'ESPECES', 'name' => 'Espèces']]);
            
        $this->apiClient
            ->shouldReceive('getInvoiceTypes')
            ->andReturn([['code' => 'FV', 'name' => 'Facture de Vente']]);

        $response = $this->get(route('sgmef.invoices.create'));

        $response->assertStatus(200);
        $response->assertViewIs('lara-sgmef-qr::invoices.create');
        $response->assertViewHas(['taxGroups', 'paymentTypes', 'invoiceTypes']);
    }

    /**
     * Test de création de facture avec données valides
     */
    public function test_can_create_invoice_with_valid_data(): void
    {
        $invoiceData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => [
                'name' => 'John Doe',
                'contact' => '+229 12345678'
            ],
            'operator' => [
                'id' => 1,
                'name' => 'Operator Test'
            ],
            'items' => [
                [
                    'name' => 'Article 1',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B'
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000
                ]
            ]
        ];

        $invoice = Invoice::factory()->make(['uid' => 'test-uid-123']);
        
        $this->invoiceManager
            ->shouldReceive('createInvoice')
            ->andReturn($invoice);

        $response = $this->post(route('sgmef.invoices.store'), $invoiceData);

        $response->assertRedirect(route('sgmef.invoices.show', 'test-uid-123'));
        $response->assertSessionHas('success');
    }

    /**
     * Test de création de facture avec données invalides
     */
    public function test_create_invoice_fails_with_invalid_data(): void
    {
        $invalidData = [
            'ifu' => '123', // IFU invalide
            'type' => 'XX', // Type invalide
            'items' => [], // Pas d'articles
        ];

        $response = $this->post(route('sgmef.invoices.store'), $invalidData);

        $response->assertSessionHasErrors(['ifu', 'type', 'items']);
    }

    /**
     * Test d'affichage d'une facture
     */
    public function test_can_view_invoice(): void
    {
        $invoice = Invoice::factory()->create(['uid' => 'test-uid-123']);
        
        $this->invoiceManager
            ->shouldReceive('getInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->get(route('sgmef.invoices.show', 'test-uid-123'));

        $response->assertStatus(200);
        $response->assertViewIs('lara-sgmef-qr::invoices.show');
        $response->assertViewHas('invoice');
    }

    /**
     * Test de confirmation de facture
     */
    public function test_can_confirm_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::CONFIRMED
        ]);
        
        $this->invoiceManager
            ->shouldReceive('confirmInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->post(route('sgmef.invoices.confirm', 'test-uid-123'));

        $response->assertRedirect(route('sgmef.invoices.show', 'test-uid-123'));
        $response->assertSessionHas('success');
    }

    /**
     * Test d'annulation de facture
     */
    public function test_can_cancel_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::CANCELLED
        ]);
        
        $this->invoiceManager
            ->shouldReceive('cancelInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->post(route('sgmef.invoices.cancel', 'test-uid-123'));

        $response->assertRedirect(route('sgmef.invoices.show', 'test-uid-123'));
        $response->assertSessionHas('success');
    }

    /**
     * Test de prévisualisation de facture
     */
    public function test_can_preview_invoice(): void
    {
        $invoiceData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => ['name' => 'John Doe'],
            'operator' => ['id' => 1, 'name' => 'Operator'],
            'items' => [
                [
                    'name' => 'Article 1',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B'
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000
                ]
            ]
        ];

        $apiResponse = new \Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto(
            'test-uid',
            1000,
            180
        );

        $this->apiClient
            ->shouldReceive('createInvoice')
            ->andReturn($apiResponse);

        $response = $this->postJson(route('sgmef.invoices.preview'), $invoiceData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_amount' => 1000,
                'total_tax_amount' => 180
            ]
        ]);
    }

    /**
     * Test de téléchargement PDF
     */
    public function test_can_download_pdf(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::CONFIRMED
        ]);
        
        $this->invoiceManager
            ->shouldReceive('getInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->get(route('sgmef.invoices.pdf', 'test-uid-123'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test de page d'impression
     */
    public function test_can_view_print_page(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-123',
            'status' => InvoiceStatusEnum::CONFIRMED
        ]);
        
        $this->invoiceManager
            ->shouldReceive('getInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->get(route('sgmef.invoices.print', 'test-uid-123'));

        $response->assertStatus(200);
        $response->assertViewHas('invoice');
    }

    /**
     * Test de synchronisation de facture
     */
    public function test_can_sync_invoice(): void
    {
        $invoice = Invoice::factory()->create(['uid' => 'test-uid-123']);
        
        $this->invoiceManager
            ->shouldReceive('syncInvoice')
            ->with('test-uid-123')
            ->andReturn($invoice);

        $response = $this->get(route('sgmef.invoices.sync', 'test-uid-123'));

        $response->assertRedirect(route('sgmef.invoices.show', 'test-uid-123'));
        $response->assertSessionHas('success');
    }
}
