<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Services\InvoiceQueueDispatcher;
use Illuminate\Bus\PendingDispatch;
use Illuminate\Foundation\Bus\PendingDispatch as FoundationPendingDispatch;
use Illuminate\Support\Facades\Bus;
use Orchestra\Testbench\TestCase;

class AfterCommitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_dispatch_returns_pending_dispatch(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);

        $pending = $dispatcher->confirmByUid('uid-test');

        // Le dispatcher retourne un PendingDispatch qui supporte afterCommit
        $this->assertInstanceOf(FoundationPendingDispatch::class, $pending);
    }

    public function test_dispatch_without_transaction_dispatches_job(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);
        $dispatcher->confirmByUid('uid-no-tx');

        Bus::assertDispatched(ConfirmInvoiceJob::class);
    }

    public function test_all_dispatcher_methods_return_pending_dispatch(): void
    {
        Bus::fake();

        $dispatcher = $this->app->make(InvoiceQueueDispatcher::class);

        $this->assertInstanceOf(
            FoundationPendingDispatch::class,
            $dispatcher->confirmByUid('uid-1')
        );
        $this->assertInstanceOf(
            FoundationPendingDispatch::class,
            $dispatcher->cancelByUid('uid-2')
        );
        $this->assertInstanceOf(
            FoundationPendingDispatch::class,
            $dispatcher->syncByUid('uid-3')
        );
    }
}
