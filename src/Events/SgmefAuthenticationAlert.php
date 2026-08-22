<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Events;

/**
 * Événement déclenché lorsqu'une authentification à l'API e-MECeF échoue.
 *
 * Permet à l'application hôte de réagir : notification administrateur,
 * basculement vers un mode dégradé, alerte de sécurité, etc.
 */
class SgmefAuthenticationAlert
{
    /**
     * @param  string  $endpoint  Endpoint API qui a échoué.
     * @param  int  $httpStatus  Code HTTP de la réponse (401 ou 403).
     * @param  string  $errorCode  Code d'erreur retourné par l'API.
     * @param  string  $message  Message d'erreur.
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly int $httpStatus,
        public readonly string $errorCode,
        public readonly string $message,
    ) {
    }
}
