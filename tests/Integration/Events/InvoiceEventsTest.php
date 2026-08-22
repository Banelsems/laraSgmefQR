<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Integration\Events;

use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\Events\InvoiceCancelled;
use Banelsems\LaraSgmefQr\Events\InvoiceConfirmed;
use Banelsems\LaraSgmefQr\Events\InvoiceCreated;
use Banelsems\LaraSgmefQr\Events\InvoiceSynchronized;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Repositories\EloquentInvoiceRepository;
use Banelsems\LaraSgmefQr\Services\InvoiceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Orchestra\Testbench\TestCase;

class InvoiceEventsTest extends TestCase
{
    use RefreshDatabase;

    private $apiClient;
    private InvoiceManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\Spatie\LaravelData\Data::class)) {
            $this->markTestSkipped('spatie/laravel-data non installé');
        }

        $this->apiClient = Mockery::mock(SgmefApiClientInterface::class);
        $this->app->instance(SgmefApiClientInterface::class, $this->apiClient);
        $repository = new EloquentInvoiceRepository();
        $this->app->instance(InvoiceRepositoryInterface::class, $repository);
        $this->manager = new InvoiceManager($this->apiClient, $repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    public function test_create_invoice_dispatches_invoice_created(): void
    {
        Event::fake([InvoiceCreated::class]);

        $dto = $this->buildValidDto();

        $this->apiClient
            ->shouldReceive('createInvoice')
            ->andReturn(new InvoiceResponseDto('uid-ev-1', 2000, 360, null, null, 'pending'));

        $this->manager->createInvoice($dto);

        Event::assertDispatched(InvoiceCreated::class, fn (InvoiceCreated $e) => $e->invoice->uid === 'uid-ev-1');
    }

    public function test_confirm_invoice_dispatches_invoice_confirmed(): void
    {
        Event::fake([InvoiceConfirmed::class]);

        Invoice::factory()->create([
            'uid' => 'uid-conf',
            'status' => \Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum::PENDING,
        ]);

        $security = new SecurityElementsDto(null, 'qr-data', 'mecf-123', null, null, null, null, false);

        $this->apiClient->shouldReceive('confirmInvoice')->with('uid-conf')->andReturn($security);

        $this->manager->confirmInvoice('uid-conf');

        Event::assertDispatched(InvoiceConfirmed::class, fn (InvoiceConfirmed $e) => $e->invoice->uid === 'uid-conf');
    }

    public function test_cancel_invoice_dispatches_invoice_cancelled(): void
    {
        Event::fake([InvoiceCancelled::class]);

        Invoice::factory()->create([
            'uid' => 'uid-cancel',
            'status' => \Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum::PENDING,
        ]);

        $this->apiClient->shouldReceive('cancelInvoice')->with('uid-cancel')->andReturn(['status' => 'cancelled']);

        $this->manager->cancelInvoice('uid-cancel');

        Event::assertDispatched(InvoiceCancelled::class, fn (InvoiceCancelled $e) => $e->invoice->uid === 'uid-cancel');
    }

    public function test_sync_invoice_dispatches_invoice_synchronized(): void
    {
        Event::fake([InvoiceSynchronized::class]);

        Invoice::factory()->create([
            'uid' => 'uid-sync',
            'status' => \Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum::CONFIRMED,
        ]);

        $apiResponse = new InvoiceResponseDto('uid-sync', 2000, 360, null, null, 'confirmed');

        $this->apiClient->shouldReceive('getInvoice')->with('uid-sync')->andReturn($apiResponse);

        $this->manager->syncInvoice('uid-sync');

        Event::assertDispatched(InvoiceSynchronized::class, fn (InvoiceSynchronized $e) => $e->invoice->uid === 'uid-sync');
    }

    private function buildValidDto(): InvoiceRequestDto
    {
        $client = new ClientDto('John Doe', null, '+229 12345678', '123 Rue Test');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        return new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );
    }
}
