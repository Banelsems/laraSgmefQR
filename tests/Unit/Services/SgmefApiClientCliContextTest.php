<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Mockery;
use Orchestra\Testbench\TestCase;

/**
 * Tests unitaires pour SgmefApiClient en contexte CLI / queue (sans Request HTTP).
 *
 * Vérifie que le client ne crash pas lorsqu'aucune requête HTTP n'est disponible,
 * ce qui était le cas avant v3.1 (request()->ip() / request()->userAgent() en CLI).
 */
class SgmefApiClientCliContextTest extends TestCase
{
    private HttpClient $httpClient;
    private SgmefApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->apiClient = new SgmefApiClient(
            $this->httpClient,
            'https://test-api.example.com',
            'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign',
            ['timeout' => 30]
        );
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

    /**
     * En contexte CLI (runningInConsole = true), l'appel à getStatus()
     * ne doit pas crasher même si aucune Request HTTP n'existe.
     */
    public function test_get_status_works_in_cli_context_without_http_request(): void
    {
        // Contexte CLI : pas de Request HTTP active
        $this->assertTrue(app()->runningInConsole(), 'Le test doit s executer en contexte CLI.');

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn(['status' => 'OK', 'ifu' => '1234567890123']);
        $response->shouldReceive('status')->andReturn(200);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->with([
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->andReturnSelf();

        $this->httpClient
            ->shouldReceive('withOptions')
            ->with(Mockery::on(fn ($opts): bool => is_array($opts) && ($opts['timeout'] ?? null) === 30))
            ->andReturnSelf();

        $this->httpClient
            ->shouldReceive('get')
            ->with('https://test-api.example.com/api/info/status', [])
            ->andReturn($response);

        // Ne doit pas lever d exception malgré l absence de Request HTTP
        $result = $this->apiClient->getStatus();

        $this->assertInstanceOf(ApiStatusDto::class, $result);
        $this->assertEquals('OK', $result->status);
    }

    /**
     * En contexte CLI, createInvoice ne doit pas crasher non plus.
     */
    public function test_create_invoice_does_not_crash_in_cli_context(): void
    {
        $this->assertTrue(app()->runningInConsole(), 'Le test doit s executer en contexte CLI.');

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn(['uid' => 'test-uid-123', 'total' => 1000]);
        $response->shouldReceive('status')->andReturn(200);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();

        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();

        $this->httpClient
            ->shouldReceive('post')
            ->andReturn($response);

        // On construit un DTO minimal valide
        $invoiceData = \Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'items' => [['name' => 'Article', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']],
            'payment' => [['name' => 'ESPECES', 'amount' => 1000]],
            'client' => ['name' => 'Client Test'],
            'operator' => ['id' => '1', 'name' => 'Opérateur'],
        ]);

        $result = $this->apiClient->createInvoice($invoiceData);

        $this->assertEquals('test-uid-123', $result->uid);
    }
}
