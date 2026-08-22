<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

use Exception;

/**
 * Exception de base pour toutes les erreurs de l'API SyGM-eMCF.
 *
 * Toutes les sous-classes d'exception restent catchables via
 * `catch (SgmefApiException $e)` pour maximiser la compatibilité.
 */
class SgmefApiException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        private readonly ?array $apiResponse = null,
        private readonly ?int $httpStatus = null,
        private readonly ?string $errorCode = null,
        private readonly array $context = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retourne la réponse brute de l'API si disponible.
     */
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }

    /**
     * Retourne le statut HTTP de la réponse si disponible.
     */
    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * Retourne le code d'erreur fiscal/API si disponible.
     */
    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Retourne le contexte structuré de l'erreur.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * Vérifie si c'est une erreur d'authentification.
     */
    public function isAuthenticationError(): bool
    {
        return $this->httpStatus !== null && in_array($this->httpStatus, [401, 403], true);
    }

    /**
     * Vérifie si c'est une erreur de validation.
     */
    public function isValidationError(): bool
    {
        return $this->httpStatus !== null && in_array($this->httpStatus, [400, 422], true);
    }

    /**
     * Vérifie si c'est une erreur serveur.
     */
    public function isServerError(): bool
    {
        return $this->httpStatus !== null && $this->httpStatus >= 500;
    }

    /**
     * Vérifie si c'est une erreur de rate limiting.
     */
    public function isRateLimitError(): bool
    {
        return $this->httpStatus === 429;
    }

    /**
     * Vérifie si c'est une erreur réseau (timeout, connexion, DNS).
     */
    public function isNetworkError(): bool
    {
        return $this instanceof SgmefNetworkException;
    }
}
