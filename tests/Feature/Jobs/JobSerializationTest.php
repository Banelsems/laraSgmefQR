<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\Jobs\CancelInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CleanupExpiredPendingInvoicesJob;
use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CreateInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\SyncInvoiceJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Orchestra\Testbench\TestCase;
use ReflectionClass;

class JobSerializationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_create_invoice_job_is_serializable_with_array_payload(): void
    {
        $payload = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'items' => [],
            'client' => ['name' => 'Test'],
            'operator' => ['name' => 'Op', 'id' => '1'],
            'payment' => [],
        ];

        $job = new CreateInvoiceJob($payload);

        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertSame($payload['ifu'], $unserialized->data['ifu']);
    }

    public function test_create_invoice_job_implements_should_queue(): void
    {
        $reflection = new ReflectionClass(CreateInvoiceJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    public function test_create_invoice_job_tries_is_one(): void
    {
        $job = new CreateInvoiceJob(['ifu' => '1234567890123']);

        // POST non-idempotent : pas de retry automatique
        $this->assertSame(1, $job->tries);
    }

    public function test_confirm_invoice_job_is_serializable_with_string_uid(): void
    {
        $job = new ConfirmInvoiceJob('test-uid-123');

        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertSame('test-uid-123', $unserialized->uid);
    }

    public function test_confirm_invoice_job_is_unique(): void
    {
        $reflection = new ReflectionClass(ConfirmInvoiceJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldBeUnique::class));
    }

    public function test_confirm_invoice_job_unique_id_is_stable(): void
    {
        $job = new ConfirmInvoiceJob('uid-abc');

        $this->assertSame('confirm:uid-abc', $job->uniqueId());
    }

    public function test_confirm_invoice_job_tries_is_one(): void
    {
        $job = new ConfirmInvoiceJob('uid-abc');

        // PUT confirm non-idempotent : pas de retry automatique
        $this->assertSame(1, $job->tries);
    }

    public function test_sync_invoice_job_is_serializable(): void
    {
        $job = new SyncInvoiceJob('sync-uid-456');

        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertSame('sync-uid-456', $unserialized->uid);
    }

    public function test_sync_invoice_job_is_unique(): void
    {
        $reflection = new ReflectionClass(SyncInvoiceJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldBeUnique::class));
    }

    public function test_sync_invoice_job_unique_id_differs_from_confirm(): void
    {
        $syncJob = new SyncInvoiceJob('uid-xyz');
        $confirmJob = new ConfirmInvoiceJob('uid-xyz');

        // Les clés uniques sont distinctes pour ne pas se bloquer mutuellement
        $this->assertNotSame($syncJob->uniqueId(), $confirmJob->uniqueId());
    }

    public function test_sync_invoice_job_allows_retry(): void
    {
        $job = new SyncInvoiceJob('uid-xyz');

        // GET sync : safe to retry
        $this->assertGreaterThan(1, $job->tries);
    }

    public function test_cancel_invoice_job_is_serializable(): void
    {
        $job = new CancelInvoiceJob('cancel-uid-789');

        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertSame('cancel-uid-789', $unserialized->uid);
    }

    public function test_cancel_invoice_job_is_unique(): void
    {
        $reflection = new ReflectionClass(CancelInvoiceJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldBeUnique::class));
    }

    public function test_cancel_invoice_job_unique_id_differs_from_confirm(): void
    {
        $cancelJob = new CancelInvoiceJob('uid-xyz');
        $confirmJob = new ConfirmInvoiceJob('uid-xyz');

        $this->assertNotSame($cancelJob->uniqueId(), $confirmJob->uniqueId());
    }

    public function test_cancel_invoice_job_tries_is_one(): void
    {
        $job = new CancelInvoiceJob('uid-abc');

        // PUT cancel non-idempotent : pas de retry automatique
        $this->assertSame(1, $job->tries);
    }

    public function test_cleanup_job_is_unique(): void
    {
        $reflection = new ReflectionClass(CleanupExpiredPendingInvoicesJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldBeUnique::class));
    }

    public function test_cleanup_job_unique_id_is_constant(): void
    {
        $job1 = new CleanupExpiredPendingInvoicesJob();
        $job2 = new CleanupExpiredPendingInvoicesJob();

        $this->assertSame($job1->uniqueId(), $job2->uniqueId());
    }
}
