<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Integration\Repositories;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Repositories\EloquentInvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class EloquentInvoiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentInvoiceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentInvoiceRepository();
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

    public function test_create_persists_invoice(): void
    {
        $invoice = $this->repository->create([
            'uid' => 'uid-abc',
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 5000,
        ]);

        $this->assertDatabaseHas('invoices', ['uid' => 'uid-abc']);
        $this->assertEquals('uid-abc', $invoice->uid);
    }

    public function test_find_by_uid_returns_invoice(): void
    {
        Invoice::factory()->create(['uid' => 'uid-find-me']);

        $invoice = $this->repository->findByUid('uid-find-me');

        $this->assertEquals('uid-find-me', $invoice->uid);
    }

    public function test_find_by_uid_throws_when_missing(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Facture introuvable');

        $this->repository->findByUid('does-not-exist');
    }

    public function test_update_modifies_attributes(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatusEnum::PENDING]);

        $updated = $this->repository->update($invoice, ['status' => InvoiceStatusEnum::CONFIRMED]);

        $this->assertEquals(InvoiceStatusEnum::CONFIRMED, $updated->status);
    }

    public function test_update_status_changes_status(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatusEnum::PENDING]);

        $updated = $this->repository->updateStatus($invoice, InvoiceStatusEnum::CANCELLED);

        $this->assertEquals(InvoiceStatusEnum::CANCELLED, $updated->status);
    }

    public function test_paginate_applies_status_filter(): void
    {
        Invoice::factory()->count(3)->create(['status' => InvoiceStatusEnum::PENDING]);
        Invoice::factory()->count(2)->create(['status' => InvoiceStatusEnum::CONFIRMED]);

        $pending = $this->repository->paginate(['status' => InvoiceStatusEnum::PENDING->value], 10);
        $confirmed = $this->repository->paginate(['status' => InvoiceStatusEnum::CONFIRMED->value], 10);

        $this->assertCount(3, $pending->items());
        $this->assertCount(2, $confirmed->items());
    }

    public function test_paginate_applies_type_filter(): void
    {
        Invoice::factory()->count(2)->create(['type' => 'FV']);
        Invoice::factory()->create(['type' => 'FA']);

        $result = $this->repository->paginate(['type' => 'FV'], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_paginate_applies_date_range(): void
    {
        Invoice::factory()->create(['created_at' => '2026-01-15']);
        Invoice::factory()->create(['created_at' => '2026-02-15']);
        Invoice::factory()->create(['created_at' => '2026-03-15']);

        $result = $this->repository->paginate(['date_from' => '2026-02-01', 'date_to' => '2026-02-28'], 10);

        $this->assertCount(1, $result->items());
    }

    public function test_paginate_rejects_unknown_sort_column(): void
    {
        Invoice::factory()->count(2)->create();

        // Une colonne non whitelistée doit être ignorée silencieusement
        $result = $this->repository->paginate(['sort' => 'malicious_column'], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_count_by_status(): void
    {
        Invoice::factory()->count(3)->create(['status' => InvoiceStatusEnum::PENDING]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::CONFIRMED]);

        $this->assertSame(3, $this->repository->countByStatus(InvoiceStatusEnum::PENDING));
        $this->assertSame(1, $this->repository->countByStatus(InvoiceStatusEnum::CONFIRMED));
    }

    public function test_find_expired_pending_returns_only_expired_pending(): void
    {
        Invoice::factory()->create([
            'status' => InvoiceStatusEnum::PENDING,
            'expires_at' => now()->subMinutes(10),
        ]);
        Invoice::factory()->create([
            'status' => InvoiceStatusEnum::PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);
        Invoice::factory()->create([
            'status' => InvoiceStatusEnum::CONFIRMED,
            'expires_at' => now()->subMinutes(10),
        ]);

        $expired = $this->repository->findExpiredPending();

        $this->assertCount(1, $expired);
    }

    // --- Tests de non-régression Lot 4.1 ---

    public function test_paginate_search_by_uid(): void
    {
        Invoice::factory()->create(['uid' => 'uid-search-me']);
        Invoice::factory()->create(['uid' => 'uid-other']);

        $result = $this->repository->paginate(['search' => 'search-me'], 10);

        $this->assertCount(1, $result->items());
        $this->assertSame('uid-search-me', $result->items()[0]->uid);
    }

    public function test_paginate_search_by_customer_ifu(): void
    {
        Invoice::factory()->create(['uid' => 'uid-a', 'customer_ifu' => '9998887776665']);
        Invoice::factory()->create(['uid' => 'uid-b', 'customer_ifu' => '1112223334445']);

        $result = $this->repository->paginate(['search' => '999888'], 10);

        $this->assertCount(1, $result->items());
        $this->assertSame('uid-a', $result->items()[0]->uid);
    }

    public function test_paginate_search_by_mecf_code(): void
    {
        Invoice::factory()->create(['uid' => 'uid-x', 'mecf_code' => 'MECF-ABC123']);
        Invoice::factory()->create(['uid' => 'uid-y', 'mecf_code' => null]);

        $result = $this->repository->paginate(['search' => 'ABC123'], 10);

        $this->assertCount(1, $result->items());
        $this->assertSame('uid-x', $result->items()[0]->uid);
    }

    public function test_paginate_search_by_reference(): void
    {
        Invoice::factory()->create(['uid' => 'uid-r1', 'reference' => 'REF-ORIGINAL-001']);
        Invoice::factory()->create(['uid' => 'uid-r2', 'reference' => null]);

        $result = $this->repository->paginate(['search' => 'ORIGINAL'], 10);

        $this->assertCount(1, $result->items());
        $this->assertSame('uid-r1', $result->items()[0]->uid);
    }

    public function test_paginate_search_by_client_name_in_json(): void
    {
        Invoice::factory()->create([
            'uid' => 'uid-n1',
            'raw_request' => ['client' => ['name' => 'Acme Corporation']],
        ]);
        Invoice::factory()->create([
            'uid' => 'uid-n2',
            'raw_request' => ['client' => ['name' => 'Other Client']],
        ]);

        $result = $this->repository->paginate(['search' => 'Acme'], 10);

        $this->assertCount(1, $result->items());
        $this->assertSame('uid-n1', $result->items()[0]->uid);
    }

    public function test_paginate_amount_min_filter(): void
    {
        Invoice::factory()->create(['total_amount' => 500]);
        Invoice::factory()->create(['total_amount' => 5000]);
        Invoice::factory()->create(['total_amount' => 50000]);

        $result = $this->repository->paginate(['amount_min' => 1000], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_paginate_amount_max_filter(): void
    {
        Invoice::factory()->create(['total_amount' => 500]);
        Invoice::factory()->create(['total_amount' => 5000]);
        Invoice::factory()->create(['total_amount' => 50000]);

        $result = $this->repository->paginate(['amount_max' => 10000], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_paginate_amount_min_and_max_range(): void
    {
        Invoice::factory()->create(['total_amount' => 100]);
        Invoice::factory()->create(['total_amount' => 1000]);
        Invoice::factory()->create(['total_amount' => 5000]);
        Invoice::factory()->create(['total_amount' => 50000]);

        $result = $this->repository->paginate(['amount_min' => 500, 'amount_max' => 10000], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_paginate_amount_min_zero_includes_all(): void
    {
        Invoice::factory()->create(['total_amount' => 0]);
        Invoice::factory()->create(['total_amount' => 5000]);

        $result = $this->repository->paginate(['amount_min' => 0], 10);

        $this->assertCount(2, $result->items());
    }
}
