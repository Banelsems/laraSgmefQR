<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur de rate limiting (429 Too Many Requests).
 *
 * L'API a limité le débit des requêtes. Le délai avant retry
 * peut être récupéré via retryAfter().
 */
class SgmefRateLimitException extends SgmefApiException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Exception $previous = null,
        ?array $apiResponse = null,
        ?int $httpStatus = null,
        ?string $errorCode = null,
        array $context = [],
        private readonly ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous, $apiResponse, $httpStatus, $errorCode, $context);
    }

    /**
     * Retourne le délai en secondes avant de retry, tel que spécifié
     * par l'en-tête HTTP Retry-After, ou null si non fourni.
     */
    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
