<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto;
use Banelsems\LaraSgmefQr\DTOs\TaxGroupDto;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Client API pour SyGM-eMCF - Responsabilité unique : communication avec l'API
 */
class SgmefApiClient implements SgmefApiClientInterface
{
    private string $baseUrl;
    private string $token;
    private array $httpOptions;

    public function __construct(
        private readonly HttpClient $httpClient,
        ?string $baseUrl = null,
        ?string $token = null,
        array $httpOptions = []
    ) {
        $this->baseUrl = rtrim($baseUrl ?? config('lara_sgmef_qr.api_url'), '/');
        $this->token = $token ?? config('lara_sgmef_qr.token');
        $this->httpOptions = array_merge([
            'timeout' => 30,
            'verify' => true,
        ], $httpOptions, config('lara_sgmef_qr.http_options', []));
    }

    public function setCredentials(string $token): void
    {
        $this->token = $token;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }


    public function createInvoice(InvoiceRequestDto $invoiceData): InvoiceResponseDto
    {
        $response = $this->makeRequest('POST', '/invoice', $invoiceData->toArray());
        
        return InvoiceResponseDto::fromArray($response);
    }

    public function getInvoice(string $uid): InvoiceResponseDto
    {
        $response = $this->makeRequest('GET', "/invoice/{$uid}");
        
        return InvoiceResponseDto::fromArray($response);
    }

    public function getStatus(): ApiStatusDto
    {
        $response = $this->makeRequest('GET', '/info/status');

        return ApiStatusDto::from($response);
    }

    /**
     * @return array<TaxGroupDto>
     */
    public function getTaxGroups(): array
    {
        $response = $this->makeRequest('GET', '/info/tax-groups');

        return TaxGroupDto::collection($response)->all();
    }

    /**
     * @return array<InvoiceTypeDto>
     */
    public function getInvoiceTypes(): array
    {
        $response = $this->makeRequest('GET', '/info/invoice-types');

        return InvoiceTypeDto::collection($response)->all();
    }

    /**
     * @return array<PaymentTypeDto>
     */
    public function getPaymentTypes(): array
    {
        $response = $this->makeRequest('GET', '/info/payment-types');

        return PaymentTypeDto::collection($response)->all();
    }

    public function confirmInvoice(string $uid, bool $withQrCode = false): array
    {
        $response = $this->makeRequest('PUT', "/invoice/{$uid}/confirm");
        
        return SecurityElementsDto::fromArray($response);
    }

    public function cancelInvoice(string $uid): array
    {
        return $this->makeRequest('PUT', "/invoice/{$uid}/cancel");
    }

    /**
     * Effectue une requête HTTP vers l'API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        try {
            Log::info("SgmefAPI Request", [
                'method' => $method,
                'url' => $url,
                'data' => $data
            ]);

            $response = $this->httpClient
                ->withHeaders($this->getHeaders())
                ->withOptions($this->httpOptions)
                ->{strtolower($method)}($url, $data);

            $this->logResponse($response, $method, $endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            $this->handleErrorResponse($response, $method, $endpoint);

        } catch (\Exception $e) {
            Log::error("SgmefAPI Exception", [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new SgmefApiException(
                "Erreur lors de la communication avec l'API SyGM-eMCF: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Retourne les headers HTTP pour les requêtes
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Log la réponse de l'API
     */
    private function logResponse(Response $response, string $method, string $endpoint): void
    {
        Log::info("SgmefAPI Response", [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'response' => $response->json()
        ]);
    }

    /**
     * Gère les réponses d'erreur de l'API
     */
    private function handleErrorResponse(Response $response, string $method, string $endpoint): void
    {
        $errorData = $response->json();
        $errorMessage = $errorData['message'] ?? 'Erreur inconnue';
        $errorCode = $errorData['code'] ?? $response->status();
        
        Log::error("SgmefAPI Error Response", [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'error' => $errorData
        ]);

        throw new SgmefApiException(
            "Erreur API SyGM-eMCF [{$errorCode}]: {$errorMessage}",
            $response->status()
        );
    }
}
