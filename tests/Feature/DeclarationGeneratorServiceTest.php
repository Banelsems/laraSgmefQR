<?php

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService;
use Illuminate\Support\Carbon;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Mockery;

class DeclarationGeneratorServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_it_generates_declaration_successfully()
    {
        // Arrange
        $invoiceManagerMock = Mockery::mock(InvoiceManagerInterface::class);
        $apiClientMock = Mockery::mock(SgmefApiClientInterface::class);

        $this->app->instance(InvoiceManagerInterface::class, $invoiceManagerMock);
        $this->app->instance(SgmefApiClientInterface::class, $apiClientMock);

        $invoice = $this->fakeInvoice();

        $invoiceManagerMock->shouldReceive('createInvoice')->andReturn($invoice);
        $invoiceManagerMock->shouldReceive('confirmInvoice')->andReturn($invoice);

        Config::set('lara_sgmef_qr.emecef_test_cases', [
            ['name' => 'Test Case 1', 'request' => [
                'type' => 'FV',
                'items' => [['name' => 'Article', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']],
                'payment' => [['name' => 'ESPECES', 'amount' => 1000]],
            ]]
        ]);
        Config::set('lara_sgmef_qr.company_info.ifu', '1234567890123');

        $service = $this->app->make(DeclarationGeneratorService::class);

        // Act
        $pdfPath = $service->generateDeclaration();

        // Assert
        $this->assertTrue(File::exists($pdfPath));
        $this->assertStringContainsString('declaration_emecef.pdf', $pdfPath);

        // Clean up
        File::delete($pdfPath);
    }

    public function test_it_handles_api_errors_and_cleans_up()
    {
        // Arrange
        $invoiceManagerMock = Mockery::mock(InvoiceManagerInterface::class);
        $apiClientMock = Mockery::mock(SgmefApiClientInterface::class);

        $this->app->instance(InvoiceManagerInterface::class, $invoiceManagerMock);
        $this->app->instance(SgmefApiClientInterface::class, $apiClientMock);

        $invoiceManagerMock->shouldReceive('createInvoice')->andReturn($this->fakeInvoice());
        $invoiceManagerMock->shouldReceive('confirmInvoice')->andThrow(new \Exception('API Error'));

        Config::set('lara_sgmef_qr.emecef_test_cases', [
            ['name' => 'Test Case 1', 'request' => [
                'type' => 'FV',
                'items' => [['name' => 'Article', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']],
                'payment' => [['name' => 'ESPECES', 'amount' => 1000]],
            ]]
        ]);
        Config::set('lara_sgmef_qr.company_info.ifu', '1234567890123');

        $service = $this->app->make(DeclarationGeneratorService::class);
        
        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Error');

        try {
            $service->generateDeclaration();
        } finally {
            $tempDir = storage_path('app/lara_sgmef_qr_temp');
            $this->assertFalse(File::exists($tempDir), 'Temporary directory should be cleaned up even after an exception.');
        }
    }

    private function fakeInvoice(): Invoice
    {
        $invoice = new Invoice([
            'uid' => 'fake-uid',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::CONFIRMED,
            'total_amount' => 1000,
            'raw_request' => [
                'items' => [
                    ['name' => 'Article', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B'],
                ],
            ],
            'raw_response' => [],
            'security_elements' => [],
        ]);

        $invoice->created_at = Carbon::parse('2026-07-02 10:00:00');
        $invoice->setAttribute('items', [
            (object) ['name' => 'Article', 'price' => 1000, 'quantity' => 1],
        ]);

        return $invoice;
    }
}
