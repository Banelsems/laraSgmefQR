<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour SgmefApiClient
 */
class SgmefApiClientTest extends TestCase
{
    private $httpClient;
    private $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->apiClient = new SgmefApiClient(
            $this->httpClient,
            'https://test-api.example.com',
            'test-token',
            ['timeout' => 30]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test de récupération du statut de l'API
     */
    public function test_can_get_status(): void
    {
        $expectedResponse = ['status' => 'OK', 'version' => '1.0'];
        
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn($expectedResponse);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->with([
                'Authorization' => 'Bearer test-token',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->with(['timeout' => 30])
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('get')
            ->with('https://test-api.example.com/info/status', [])
            ->andReturn($response);

        $result = $this->apiClient->getStatus();
        
        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Test de création de facture
     */
    public function test_can_create_invoice(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        $invoiceData = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $expectedResponse = [
            'uid' => 'test-uid-123',
            'totalAmount' => 1000,
            'totalTaxAmount' => 180,
            'status' => 'pending'
        ];
        
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn($expectedResponse);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('post')
            ->with('https://test-api.example.com/invoice', $invoiceData->toArray())
            ->andReturn($response);

        $result = $this->apiClient->createInvoice($invoiceData);
        
        $this->assertEquals('test-uid-123', $result->uid);
        $this->assertEquals(1000, $result->totalAmount);
        $this->assertEquals(180, $result->totalTaxAmount);
    }

    /**
     * Test de confirmation de facture
     */
    public function test_can_confirm_invoice(): void
    {
        $uid = 'test-uid-123';
        $expectedResponse = [
            'dateTime' => '2023-07-02T15:22:34+00:00',
            'qrCode' => 'QR123456789',
            'codeMECeFDGI' => 'MECF123456',
            'counters' => 'CTR123',
            'nim' => 'NIM123456',
            'errorCode' => 'OK'
        ];
        
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn($expectedResponse);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('put')
            ->with('https://test-api.example.com/invoice/test-uid-123/confirm', [])
            ->andReturn($response);

        $result = $this->apiClient->confirmInvoice($uid);
        
        $this->assertEquals('2023-07-02T15:22:34+00:00', $result->dateTime);
        $this->assertEquals('QR123456789', $result->qrCode);
        $this->assertEquals('MECF123456', $result->codeMECeFDGI);
        $this->assertFalse($result->hasError());
    }

    /**
     * Test de gestion d'erreur HTTP
     */
    public function test_throws_exception_on_http_error(): void
    {
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(false);
        $response->shouldReceive('status')->andReturn(400);
        $response->shouldReceive('json')->andReturn(['message' => 'Bad Request']);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('get')
            ->andReturn($response);

        $this->expectException(SgmefApiException::class);
        $this->expectExceptionMessage('Erreur API SyGM-eMCF (400): Bad Request');
        
        $this->apiClient->getStatus();
    }

    /**
     * Test de configuration des credentials
     */
    public function test_can_set_credentials(): void
    {
        $this->apiClient->setCredentials('new-token');
        
        // Vérifier que le nouveau token est utilisé
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn(['status' => 'OK']);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->with([
                'Authorization' => 'Bearer new-token',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('get')
            ->andReturn($response);

        $this->apiClient->getStatus();
    }

    /**
     * Test de configuration de l'URL de base
     */
    public function test_can_set_base_url(): void
    {
        $this->apiClient->setBaseUrl('https://new-api.example.com');
        
        // Vérifier que la nouvelle URL est utilisée
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn(['status' => 'OK']);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('get')
            ->with('https://new-api.example.com/info/status', [])
            ->andReturn($response);

        $this->apiClient->getStatus();
    }

    /**
     * Test de récupération des groupes de taxes
     */
    public function test_can_get_tax_groups(): void
    {
        $expectedResponse = [
            ['code' => 'A', 'name' => 'Exonéré', 'rate' => 0],
            ['code' => 'B', 'name' => 'TVA 18%', 'rate' => 18]
        ];
        
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('successful')->andReturn(true);
        $response->shouldReceive('json')->andReturn($expectedResponse);

        $this->httpClient
            ->shouldReceive('withHeaders')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('withOptions')
            ->andReturnSelf();
            
        $this->httpClient
            ->shouldReceive('get')
            ->with('https://test-api.example.com/info/taxGroups', [])
            ->andReturn($response);

        $result = $this->apiClient->getTaxGroups();
        
        $this->assertEquals($expectedResponse, $result);
    }
}
