<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Events\InvoiceExpired;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\ExpirePendingInvoices;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class ExpirationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../../src/Database/migrations');
    }

    public function test_expired_pending_invoice_is_marked_expired(): void
    {
        $invoice = Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, false);

        $this->assertSame(1, $result['expired']);
        $this->assertSame(InvoiceStatusEnum::EXPIRED, $invoice->fresh()->status);
        $this->assertSame('EXPIRED_PENDING', $invoice->fresh()->error_code);
    }

    public function test_future_pending_invoice_is_not_expired(): void
    {
        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->addMinutes(5),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, false);

        $this->assertSame(0, $result['expired']);
    }

    public function test_confirmed_invoice_is_not_expired(): void
    {
        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::CONFIRMED,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, false);

        $this->assertSame(0, $result['expired']);
    }

    public function test_cancelled_invoice_is_not_expired(): void
    {
        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::CANCELLED,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, false);

        $this->assertSame(0, $result['expired']);
    }

    public function test_dry_run_does_not_modify(): void
    {
        $invoice = Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, true);

        $this->assertSame(0, $result['expired']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(InvoiceStatusEnum::PENDING, $invoice->fresh()->status);
    }

    public function test_invoice_expired_event_is_dispatched(): void
    {
        Event::fake([InvoiceExpired::class]);

        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $service->execute(500, false);

        Event::assertDispatched(InvoiceExpired::class);
    }

    public function test_dry_run_does_not_dispatch_event(): void
    {
        Event::fake([InvoiceExpired::class]);

        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $service->execute(500, true);

        Event::assertNotDispatched(InvoiceExpired::class);
    }

    public function test_batch_processing_multiple_invoices(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Invoice::create([
                'ifu' => '1234567890123',
                'type' => 'FV',
                'status' => InvoiceStatusEnum::PENDING,
                'total_amount' => 1000,
                'expires_at' => now()->subMinute(),
            ]);
        }

        $service = $this->app->make(ExpirePendingInvoices::class);
        $result = $service->execute(500, false);

        $this->assertSame(5, $result['expired']);
    }

    public function test_expired_invoice_is_not_hard_deleted(): void
    {
        $invoice = Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $service = $this->app->make(ExpirePendingInvoices::class);
        $service->execute(500, false);

        $this->assertNotNull(Invoice::find($invoice->id));
    }
}
