<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Events\InvoiceCancelled;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Illuminate\Support\Facades\Event;
use Mockery;
use Orchestra\Testbench\TestCase;

class CancelInvoiceByPostTest extends TestCase
{
    private InvoiceManager $manager;
    private $apiClient;
    private $repository;

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = Mockery::mock(SgmefApiClientInterface::class);
        $this->repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $this->manager = new InvoiceManager($this->apiClient, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cancel_invoice_by_post_calls_post_endpoint(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatusEnum::PENDING, 'uid-123');

        $this->repository
            ->shouldReceive('findByUid')->with('uid-123')->andReturn($invoice);

        $this->apiClient
            ->shouldReceive('cancelInvoiceByPost')
            ->with('uid-123')
            ->andReturn(['status' => 'cancelled']);

        $this->repository
            ->shouldReceive('update')
            ->with($invoice, Mockery::on(fn ($data) => $data['status'] === InvoiceStatusEnum::CANCELLED))
            ->andReturn($invoice);

        Event::fake([InvoiceCancelled::class]);

        $result = $this->manager->cancelInvoiceByPost('uid-123');

        $this->assertSame($invoice, $result);
        Event::assertDispatched(InvoiceCancelled::class);
    }

    public function test_cancel_invoice_by_post_rejects_cancelled_invoice(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatusEnum::CANCELLED, 'uid-456');

        $this->repository
            ->shouldReceive('findByUid')->with('uid-456')->andReturn($invoice);

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('ne peut pas être annulée');

        $this->manager->cancelInvoiceByPost('uid-456');
    }

    public function test_cancel_invoice_by_post_rejects_error_status(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatusEnum::ERROR, 'uid-789');

        $this->repository
            ->shouldReceive('findByUid')->with('uid-789')->andReturn($invoice);

        $this->expectException(InvoiceException::class);

        $this->manager->cancelInvoiceByPost('uid-789');
    }

    public function test_cancel_invoice_by_post_allows_confirmed(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatusEnum::CONFIRMED, 'uid-conf');

        $this->repository
            ->shouldReceive('findByUid')->with('uid-conf')->andReturn($invoice);

        $this->apiClient
            ->shouldReceive('cancelInvoiceByPost')
            ->with('uid-conf')
            ->andReturn(['status' => 'cancelled']);

        $this->repository
            ->shouldReceive('update')
            ->andReturn($invoice);

        Event::fake();

        $result = $this->manager->cancelInvoiceByPost('uid-conf');

        $this->assertSame($invoice, $result);
    }

    public function test_cancel_invoice_by_post_wraps_api_exception(): void
    {
        $invoice = $this->makeInvoice(InvoiceStatusEnum::PENDING, 'uid-err');
        $invoice->uid = 'uid-err';

        $this->repository
            ->shouldReceive('findByUid')->with('uid-err')->andReturn($invoice);

        $this->apiClient
            ->shouldReceive('cancelInvoiceByPost')
            ->andThrow(new \Banelsems\LaraSgmefQr\Exceptions\SgmefApiException('API error'));

        $this->repository
            ->shouldReceive('update')
            ->andReturn($invoice);

        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Erreur lors de l\'annulation');

        $this->manager->cancelInvoiceByPost('uid-err');
    }

    private function makeInvoice(InvoiceStatusEnum $status, string $uid): Invoice
    {
        $invoice = new Invoice();
        $invoice->id = 1;
        $invoice->uid = $uid;
        $invoice->status = $status;
        $invoice->raw_response = [];

        return $invoice;
    }
}
