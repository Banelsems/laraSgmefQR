<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Contracts\SleeperInterface;
use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\DTOs\TaxGroupDto;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNetworkException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNotFoundException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefRateLimitException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefServerException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefTimeoutException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefValidationException;
use Banelsems\LaraSgmefQr\Infrastructure\NativeSleeper;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefLogSanitizer;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefRetryPolicy;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Client API pour SyGM-eMCF.
 *
 * Responsabilités : construction requête, envoi HTTP, parsing réponse,
 * classification des erreurs de transport/API.
 *
 * Ne fait pas : logique métier facture, persistance Invoice, calcul taxes,
 * modification de configuration globale.
 */
class SgmefApiClient implements SgmefApiClientInterface
{
    private string $baseUrl;
    private string $token;
    private array $httpOptions;

    private readonly SgmefLogSanitizer $sanitizer;
    private readonly SgmefRetryPolicy $retryPolicy;
    private readonly SleeperInterface $sleeper;

    public function __construct(
        private readonly HttpClient $httpClient,
        ?string $baseUrl = null,
        ?string $token = null,
        array $httpOptions = [],
        ?SleeperInterface $sleeper = null
    ) {
        $rawUrl = $baseUrl ?? config('lara_sgmef_qr.api_url');
        $this->baseUrl = str_ends_with($rawUrl, '/api') ? $rawUrl : rtrim($rawUrl, '/') . '/api';
        $this->token = $token ?? config('lara_sgmef_qr.token');
        $this->httpOptions = array_merge([
            'timeout' => 30,
            'verify' => true,
        ], $httpOptions, config('lara_sgmef_qr.http_options', []));

        $this->sanitizer = new SgmefLogSanitizer();
        $this->sleeper = $sleeper ?? new NativeSleeper();
        $this->retryPolicy = new SgmefRetryPolicy();
    }

    /**
     * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
     *             Utiliser SgmefApiClientFactory::make() avec une
     *             SgmefConnectionConfig immutable.
     */
    public function setCredentials(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
     *             Utiliser SgmefApiClientFactory::make() avec une
     *             SgmefConnectionConfig immutable.
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = str_ends_with($baseUrl, '/api') ? $baseUrl : rtrim($baseUrl, '/') . '/api';
    }

    public function createInvoice(InvoiceRequestDto $invoiceData): InvoiceResponseDto
    {
        $response = $this->makeRequest('POST', '/invoice', $invoiceData->toArray());
        $dto = InvoiceResponseDto::from($response);

        if ($dto->hasError()) {
            throw new SgmefApiException(
                "Erreur API SyGM-eMCF [{$dto->errorCode}]: {$dto->errorDesc}",
                0,
                null,
                $response,
                null,
                $dto->errorCode
            );
        }

        return $dto;
    }

    public function getInvoice(string $uid): InvoiceResponseDto
    {
        $response = $this->makeRequest('GET', "/invoice/{$uid}");

        return InvoiceResponseDto::from($response);
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
        $response = $this->makeRequest('GET', '/info/taxGroups');

        return collect($response)
            ->map(fn ($rate, string $code) => TaxGroupDto::from([
                'code' => strtoupper($code),
                'name' => strtoupper($code),
                'rate' => (float) $rate,
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<InvoiceTypeDto>
     */
    public function getInvoiceTypes(): array
    {
        $response = $this->makeRequest('GET', '/info/invoiceTypes');

        return InvoiceTypeDto::collection($response)->all();
    }

    /**
     * @return array<PaymentTypeDto>
     */
    public function getPaymentTypes(): array
    {
        $response = $this->makeRequest('GET', '/info/paymentTypes');

        return PaymentTypeDto::collection($response)->all();
    }

    public function confirmInvoice(string $uid, bool $withQrCode = false): SecurityElementsDto
    {
        $response = $this->makeRequest('PUT', "/invoice/{$uid}/confirm");
        $dto = SecurityElementsDto::from($response);

        if ($dto->hasError()) {
            throw new SgmefApiException(
                "Erreur API SyGM-eMCF [{$dto->errorCode}]: {$dto->errorDesc}",
                0,
                null,
                $response,
                null,
                $dto->errorCode
            );
        }

        return $dto;
    }

    public function cancelInvoice(string $uid): array
    {
        return $this->makeRequest('PUT', "/invoice/{$uid}/cancel");
    }

    /**
     * Effectue une requête HTTP vers l'API avec retry sur opérations sûres.
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $attempt = 0;
        $maxAttempts = $this->retryPolicy->isRetryable($method, $endpoint) ? 2 : 1;

        while (true) {
            $attempt++;
            $requestId = (string) Str::uuid();

            try {
                return $this->executeRequest($method, $endpoint, $data, $requestId);
            } catch (SgmefApiException $e) {
                // Ne pas re-wrapper une SgmefApiException déjà classifiée
                if ($this->retryPolicy->shouldRetry($method, $endpoint, $e, $attempt)) {
                    $delay = $this->retryPolicy->delayFor($e, $attempt);
                    Log::info('SgmefAPI retry', $this->sanitizer->sanitize([
                        'request_id' => $requestId,
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'delay_ms' => $delay,
                        'error' => $e->getMessage(),
                    ]));

                    $this->sleeper->sleepMilliseconds($delay);
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * Exécute une seule requête HTTP.
     *
     * @return array<string, mixed>
     */
    private function executeRequest(string $method, string $endpoint, array $data, string $requestId): array
    {
        $url = $this->baseUrl . $endpoint;

        $metadata = $this->requestMetadata();

        $context = $this->sanitizer->sanitize([
            'request_id' => $requestId,
            'method' => $method,
            'endpoint' => $endpoint,
            'ip' => $metadata['ip'],
            'user_agent' => $metadata['user_agent'],
            'payload_keys' => array_keys($data),
        ]);

        Log::info('SgmefAPI Request', $context);

        try {
            $response = $this->httpClient
                ->withHeaders($this->getHeaders())
                ->withOptions($this->httpOptions)
                ->{strtolower($method)}($url, $data);
        } catch (ConnectionException $e) {
            Log::error('SgmefAPI connection error', $this->sanitizer->sanitize([
                'request_id' => $requestId,
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]));

            // Distinguer timeout de connexion impossible
            if (str_contains(strtolower($e->getMessage()), 'timeout')) {
                throw new SgmefTimeoutException(
                    "Timeout lors de la communication avec l'API SyGM-eMCF: " . $e->getMessage(),
                    0,
                    $e,
                    null,
                    null,
                    null,
                    ['request_id' => $requestId, 'endpoint' => $endpoint]
                );
            }

            throw new SgmefNetworkException(
                "Erreur réseau lors de la communication avec l'API SyGM-eMCF: " . $e->getMessage(),
                0,
                $e,
                null,
                null,
                null,
                ['request_id' => $requestId, 'endpoint' => $endpoint]
            );
        }

        $this->logResponse($response, $method, $endpoint, $requestId);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $this->throwClassifiedException($response, $method, $endpoint, $requestId);

        // unreachable — throwClassifiedException always throws
        throw new SgmefApiException('Unreachable');
    }

    /**
     * Retourne les headers HTTP pour les requêtes.
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
     * Récupère les métadonnées de la requête HTTP courante de façon sûre.
     *
     * En contexte HTTP, retourne l'IP et le user-agent de la Request courante.
     * En contexte CLI / queue / scheduler / test (sans Request active),
     * retourne des valeurs null afin de ne jamais crasher l'appel e-MECeF.
     *
     * @return array{ip: string|null, user_agent: string|null}
     */
    private function requestMetadata(): array
    {
        if (!$this->hasHttpContext()) {
            return ['ip' => null, 'user_agent' => null];
        }

        $request = request();

        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }

    /**
     * Détermine si une requête HTTP est disponible dans le contexte courant.
     */
    private function hasHttpContext(): bool
    {
        return app()->runningInConsole() === false
            && app()->has('request')
            && request() !== null;
    }

    /**
     * Log la réponse de l'API (sans payload complet, sans secrets).
     */
    private function logResponse(Response $response, string $method, string $endpoint, string $requestId): void
    {
        Log::info('SgmefAPI Response', $this->sanitizer->sanitize([
            'request_id' => $requestId,
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'response_keys' => array_keys($response->json() ?? []),
        ]));
    }

    /**
     * Classifie une réponse d'erreur HTTP en exception spécifique.
     *
     * Mapping :
     * - 401/403 → SgmefAuthenticationException
     * - 400/422 → SgmefValidationException
     * - 404     → SgmefNotFoundException
     * - 429     → SgmefRateLimitException (avec Retry-After)
     * - 5xx     → SgmefServerException
     * - autre   → SgmefApiException (base)
     */
    private function throwClassifiedException(Response $response, string $method, string $endpoint, string $requestId): void
    {
        $status = $response->status();
        $errorData = $response->json() ?? [];
        $errorMessage = is_array($errorData) ? ($errorData['message'] ?? 'Erreur inconnue') : 'Erreur inconnue';
        $errorCode = is_array($errorData) ? (string) ($errorData['code'] ?? (string) $status) : (string) $status;

        $context = [
            'request_id' => $requestId,
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ];

        Log::error('SgmefAPI Error Response', $this->sanitizer->sanitize($context));

        $message = "Erreur API SyGM-eMCF [{$errorCode}]: {$errorMessage}";

        match (true) {
            in_array($status, [401, 403], true) => throw new SgmefAuthenticationException(
                $message, 0, null, $errorData, $status, $errorCode, $context
            ),
            in_array($status, [400, 422], true) => throw new SgmefValidationException(
                $message, 0, null, $errorData, $status, $errorCode, $context
            ),
            $status === 404 => throw new SgmefNotFoundException(
                $message, 0, null, $errorData, $status, $errorCode, $context
            ),
            $status === 429 => throw new SgmefRateLimitException(
                $message, 0, null, $errorData, $status, $errorCode, $context,
                $this->parseRetryAfter($response)
            ),
            $status >= 500 => throw new SgmefServerException(
                $message, 0, null, $errorData, $status, $errorCode, $context
            ),
            default => throw new SgmefApiException(
                $message, $status, null, $errorData, $status, $errorCode, $context
            ),
        };
    }

    /**
     * Extrait le délai Retry-After de la réponse HTTP.
     */
    private function parseRetryAfter(Response $response): ?int
    {
        $retryAfter = $response->header('Retry-After');

        if ($retryAfter === '') {
            return null;
        }

        if (is_numeric($retryAfter)) {
            return (int) $retryAfter;
        }

        // Format HTTP-date : non supporté, retourne null
        return null;
    }
}
