<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Models;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Orchestra\Testbench\TestCase;

/**
 * Tests unitaires pour Invoice::requireUid().
 *
 * Vérifie qu'une facture sans UID e-MECeF ne peut pas être confirmée,
 * annulée ni synchronisée, et que requireUid() lève une exception métier
 * explicite plutôt que de propager un null vers l'API.
 */
class InvoiceUidRequirementTest extends TestCase
{
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

    public function test_require_uid_returns_uid_when_present(): void
    {
        $invoice = new Invoice([
            'uid' => 'imcf-uid-12345',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
        ]);

        $this->assertEquals('imcf-uid-12345', $invoice->requireUid());
    }

    public function test_require_uid_throws_when_uid_is_null(): void
    {
        $invoice = new Invoice([
            'uid' => null,
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::ERROR,
            'total_amount' => 1000,
        ]);
        $invoice->id = 42;

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('n\'a pas d\'UID e-MECeF');

        $invoice->requireUid();
    }

    public function test_require_uid_throws_for_fresh_invoice_without_id(): void
    {
        $invoice = new Invoice([
            'uid' => null,
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::ERROR,
            'total_amount' => 1000,
        ]);

        $this->expectException(InvoiceException::class);

        $invoice->requireUid();
    }
}
