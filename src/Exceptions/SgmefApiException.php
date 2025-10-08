<?php

namespace Banelsems\LaraSgmefQr\Exceptions;

use Exception;

/**
 * Exception spécifique pour les erreurs de l'API SyGM-eMCF
 */
class SgmefApiException extends Exception
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Exception $previous = null,
        private readonly ?array $apiResponse = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retourne la réponse brute de l'API si disponible
     */
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }

    /**
     * Vérifie si c'est une erreur d'authentification
     */
    public function isAuthenticationError(): bool
    {
        return $this->getCode() === 401;
    }

    /**
     * Vérifie si c'est une erreur de validation
     */
    public function isValidationError(): bool
    {
        return $this->getCode() === 422;
    }

    /**
     * Vérifie si c'est une erreur serveur
     */
    public function isServerError(): bool
    {
        return $this->getCode() >= 500;
    }
}
