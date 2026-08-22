<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Orchestra\Testbench\TestCase;

class InvoiceVerifyEndpointTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_verify_endpoint_returns_json_result(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'test-uid-verify',
            'status' => InvoiceStatusEnum::CONFIRMED,
            'qr_code_data' => 'test-uid-verify',
            'mecf_code' => 'MECF123',
            'security_elements' => [
                'dateTime' => '2024-01-01T12:00:00+00:00',
                'qrCode' => 'test-uid-verify',
                'codeMECeFDGI' => 'MECF123',
                'counters' => 'CTR456',
                'nim' => 'NIM789',
                'errorCode' => 'OK',
            ],
        ]);

        $manager = Mockery::mock(InvoiceManagerInterface::class);
        $manager->shouldReceive('getInvoice')->with('test-uid-verify')->andReturn($invoice);
        $this->app->instance(InvoiceManagerInterface::class, $manager);

        $response = $this->getJson(route('sgmef.invoices.verify', 'test-uid-verify'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['valid', 'verificationUrl', 'checks', 'issues'],
        ]);
    }

    public function test_verify_endpoint_returns_400_for_missing_invoice(): void
    {
        $manager = Mockery::mock(InvoiceManagerInterface::class);
        $manager->shouldReceive('getInvoice')
            ->andThrow(new \Banelsems\LaraSgmefQr\Exceptions\InvoiceException('Not found'));
        $this->app->instance(InvoiceManagerInterface::class, $manager);

        $response = $this->getJson(route('sgmef.invoices.verify', 'nonexistent-uid'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_verify_endpoint_reports_invalid_for_pending_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'uid' => 'pending-uid',
            'status' => InvoiceStatusEnum::PENDING,
            'qr_code_data' => null,
            'mecf_code' => null,
            'security_elements' => null,
        ]);

        $manager = Mockery::mock(InvoiceManagerInterface::class);
        $manager->shouldReceive('getInvoice')->with('pending-uid')->andReturn($invoice);
        $this->app->instance(InvoiceManagerInterface::class, $manager);

        $response = $this->getJson(route('sgmef.invoices.verify', 'pending-uid'));

        $response->assertStatus(200);
        $response->assertJsonPath('data.valid', false);
    }
}
