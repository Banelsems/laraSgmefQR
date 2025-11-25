<?php

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Spatie\Browsershot\Facades\Browsershot;
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

        $invoiceManagerMock->shouldReceive('createInvoice')->andReturn((object)['uid' => 'fake-uid']);
        $apiClientMock->shouldReceive('confirmInvoice')->andReturn((object)['toArray' => function() { return []; }]);

        Browsershot::shouldReceive('html->save')->once();

        Config::set('lara_sgmef_qr.emecef_test_cases', [
            ['name' => 'Test Case 1', 'request' => []]
        ]);
        Config::set('lara_sgmef_qr.company_info.ifu', '123456789');

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

        $invoiceManagerMock->shouldReceive('createInvoice')->andReturn((object)['uid' => 'fake-uid']);
        $apiClientMock->shouldReceive('confirmInvoice')->andThrow(new \Exception('API Error'));

        Config::set('lara_sgmef_qr.emecef_test_cases', [
            ['name' => 'Test Case 1', 'request' => []]
        ]);
        Config::set('lara_sgmef_qr.company_info.ifu', '123456789');

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
}
