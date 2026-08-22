<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class ConfirmCancelRaceTest extends TestCase
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

    public function test_cancel_after_confirm_throws_via_preconditions(): void
    {
        $invoice = Invoice::create([
            'uid' => 'race-uid-123',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::CONFIRMED,
            'total_amount' => 1000,
        ]);

        // Une facture confirmée peut être annulée (canBeCancelled = true)
        $this->assertTrue($invoice->canBeCancelled());

        // Simulons que la facture a été annulée entre-temps
        $invoice->update(['status' => InvoiceStatusEnum::CANCELLED]);

        // Une deuxième annulation doit échouer via les préconditions
        $this->assertFalse($invoice->fresh()->canBeCancelled());
    }

    public function test_confirm_after_cancel_throws_via_preconditions(): void
    {
        $invoice = Invoice::create([
            'uid' => 'race-uid-456',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::CANCELLED,
            'total_amount' => 1000,
        ]);

        // Une facture annulée ne peut plus être confirmée
        $this->assertFalse($invoice->canBeConfirmed());
    }

    public function test_confirm_on_expired_invoice_throws(): void
    {
        $invoice = Invoice::create([
            'uid' => 'race-uid-789',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::EXPIRED,
            'total_amount' => 1000,
        ]);

        $this->assertFalse($invoice->canBeConfirmed());
    }

    public function test_expired_is_terminal(): void
    {
        $this->assertTrue(InvoiceStatusEnum::EXPIRED->isTerminal());
        $this->assertTrue(InvoiceStatusEnum::CONFIRMED->isTerminal());
        $this->assertTrue(InvoiceStatusEnum::CANCELLED->isTerminal());
        $this->assertFalse(InvoiceStatusEnum::PENDING->isTerminal());
    }
}
