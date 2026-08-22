<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

use Banelsems\LaraSgmefQr\Exceptions\SgmefNetworkException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefRateLimitException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefServerException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefTimeoutException;

/**
 * Politique de retry pour les appels à l'API e-MECeF.
 *
 * Classification de l'idempotence :
 * - GET (status, taxGroups, invoiceTypes, paymentTypes, getInvoice) : safe to retry
 * - POST /invoice : NON idempotent — JAMAIS de retry automatique
 * - PUT /invoice/{uid}/confirm : NON retryé par défaut (l'API ne garantit
 *   pas l'idempotence documentée)
 * - PUT /invoice/{uid}/cancel : NON retryé par défaut
 *
 * La facturation est sensible aux duplications : un retry sur POST /invoice
 * pourrait créer une facture en double. La sécurité prime sur la disponibilité.
 */
final class SgmefRetryPolicy
{
    /**
     * Opérations safe-to-retry (GET uniquement).
     */
    private const SAFE_OPERATIONS = [
        'GET /info/status',
        'GET /info/taxGroups',
        'GET /info/invoiceTypes',
        'GET /info/paymentTypes',
        'GET /invoice',
    ];

    public function __construct(
        private readonly int $maxAttempts = 2,
        private readonly int $baseDelayMs = 500
    ) {
    }

    /**
     * Détermine si une opération peut être retryée.
     *
     * @param  string  $method  Méthode HTTP (GET, POST, PUT, DELETE)
     * @param  string  $endpoint  Endpoint normalisé (ex: '/invoice', '/info/status')
     */
    public function isRetryable(string $method, string $endpoint): bool
    {
        $operation = strtoupper($method) . ' ' . $this->normalizeEndpoint($endpoint);

        return in_array($operation, self::SAFE_OPERATIONS, true);
    }

    /**
     * Détermine si une exception justifie un retry, en combinant
     * l'idempotence de l'opération et le type d'erreur.
     *
     * @param  string  $method  Méthode HTTP
     * @param  string  $endpoint  Endpoint normalisé
     * @param  \Throwable  $exception  Exception survenue
     * @param  int  $attempt  Numéro de la tentative actuelle (1-based)
     */
    public function shouldRetry(string $method, string $endpoint, \Throwable $exception, int $attempt): bool
    {
        if (!$this->isRetryable($method, $endpoint)) {
            return false;
        }

        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        // Seules les erreurs transitoires justifient un retry
        return $this->isTransientException($exception);
    }

    /**
     * Calcule le délai avant la prochaine tentative.
     *
     * Pour 429, respecte Retry-After si disponible (plafonné à 30s
     * pour éviter de bloquer une requête interactive trop longtemps).
     *
     * @param  \Throwable  $exception  Exception survenue
     * @param  int  $attempt  Numéro de la tentative actuelle (1-based)
     */
    public function delayFor(\Throwable $exception, int $attempt): int
    {
        if ($exception instanceof SgmefRateLimitException) {
            $retryAfter = $exception->retryAfter();
            if ($retryAfter !== null && $retryAfter > 0) {
                return min($retryAfter, 30) * 1000;
            }
        }

        // Backoff exponentiel : base * 2^(attempt-1)
        return $this->baseDelayMs * (1 << max(0, $attempt - 1));
    }

    /**
     * Vérifie si une exception est transitoire (retryable).
     */
    private function isTransientException(\Throwable $exception): bool
    {
        return $exception instanceof SgmefNetworkException
            || $exception instanceof SgmefTimeoutException
            || $exception instanceof SgmefServerException
            || $exception instanceof SgmefRateLimitException;
    }

    /**
     * Normalise un endpoint pour la comparaison.
     * Remplace les paramètres variables par un placeholder.
     */
    private function normalizeEndpoint(string $endpoint): string
    {
        // /invoice/{uid} → /invoice
        // /invoice/{uid}/confirm → /invoice (conservatif : non retryable)
        if (preg_match('#^/invoice(/|$)#', $endpoint)) {
            return '/invoice';
        }

        return $endpoint;
    }
}
