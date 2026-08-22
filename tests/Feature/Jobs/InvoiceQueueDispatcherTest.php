<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Jobs\CancelInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CreateInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\SyncInvoiceJob;
use Banelsems\LaraSgmefQr\Services\InvoiceQueueDispatcher;
use Illuminate\Support\Facades\Bus;
use Orchestra\Testbench\TestCase;

class InvoiceQueueDispatcherTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_dispatcher_is_resolvable_from_container(): void
    {
        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);

        $this->assertInstanceOf(InvoiceQueueDispatcher::class, $dispatcher);
    }

    public function test_create_dispatches_create_invoice_job(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);

        $dto = $this->buildValidDto();
        $dispatcher->create($dto);

        Bus::assertDispatched(CreateInvoiceJob::class);
    }

    public function test_confirm_by_uid_dispatches_confirm_job(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);
        $dispatcher->confirmByUid('uid-confirm-123');

        Bus::assertDispatched(ConfirmInvoiceJob::class, function (ConfirmInvoiceJob $job) {
            return $job->uid === 'uid-confirm-123';
        });
    }

    public function test_cancel_by_uid_dispatches_cancel_job(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);
        $dispatcher->cancelByUid('uid-cancel-456');

        Bus::assertDispatched(CancelInvoiceJob::class, function (CancelInvoiceJob $job) {
            return $job->uid === 'uid-cancel-456';
        });
    }

    public function test_sync_by_uid_dispatches_sync_job(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);
        $dispatcher->syncByUid('uid-sync-789');

        Bus::assertDispatched(SyncInvoiceJob::class, function (SyncInvoiceJob $job) {
            return $job->uid === 'uid-sync-789';
        });
    }

    public function test_create_captures_ifu_in_payload(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);

        // Simuler un contexte request avec IFU A
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');

        $dto = $this->buildValidDto('9876543210987');
        $dispatcher->create($dto);

        // Le payload doit contenir l'IFU figé au moment du dispatch
        Bus::assertDispatched(CreateInvoiceJob::class, function (CreateInvoiceJob $job) {
            return $job->data['ifu'] === '9876543210987';
        });
    }

    private function buildValidDto(string $ifu = '1234567890123'): InvoiceRequestDto
    {
        return InvoiceRequestDto::from([
            'ifu' => $ifu,
            'type' => 'FV',
            'items' => [
                [
                    'name' => 'Test item',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'A',
                ],
            ],
            'client' => [
                'name' => 'Test Client',
            ],
            'operator' => [
                'name' => 'Test Operator',
                'id' => '1',
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000,
                ],
            ],
        ]);
    }
}
