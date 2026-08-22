<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Garde de sécurité pour les appels à l'API e-MECeF.
 *
 * Responsabilités :
 * - Pré-validation du token JWT avant chaque requête (évite un aller-retour 401)
 * - Rate limiting outgoing : limite configurable de requêtes par minute
 *
 * Ne fait pas : signature des requêtes (l'API e-MECeF utilise HTTPS + Bearer,
 * pas de HMAC), logique métier, persistance.
 */
final class SgmefSecurityGuard
{
    public const RATE_LIMIT_KEY = 'sgmef_api_outgoing';

    public function __construct(
        private readonly int $maxRequestsPerMinute = 60
    ) {
    }

    /**
     * Valide le format du token avant l'envoi de la requête.
     *
     * Un token e-MECeF valide est un JWT (3 segments séparés par des points)
     * ou au minimum une chaîne non vide de 20+ caractères.
     *
     * @throws SgmefAuthenticationException si le token est vide ou mal formé.
     */
    public function validateToken(string $token): void
    {
        if ($token === '' || trim($token) === '') {
            throw new SgmefAuthenticationException(
                'Token e-MECeF manquant ou vide. Configurez SGMEF_TOKEN.',
                0,
                null,
                null,
                401,
                'TOKEN_MISSING',
                ['guard' => 'token_validation']
            );
        }

        // JWT format : header.payload.signature (3 segments base64)
        $segments = explode('.', $token);
        if (count($segments) === 3) {
            return; // Format JWT valide — pas de vérification de signature côté client
        }

        // Token non-JWT : accepter les longs tokens opaques (>= 20 chars)
        if (strlen($token) >= 20) {
            return;
        }

        throw new SgmefAuthenticationException(
            'Token e-MECeF mal formé (trop court ou format invalide).',
            0,
            null,
            null,
            401,
            'TOKEN_INVALID_FORMAT',
            ['guard' => 'token_validation', 'token_length' => strlen($token)]
        );
    }

    /**
     * Vérifie que la limite de taux outgoing n'est pas dépassée.
     *
     * @throws SgmefAuthenticationException si la limite est atteinte.
     */
    public function enforceRateLimit(): void
    {
        if ($this->maxRequestsPerMinute <= 0) {
            return; // Rate limiting désactivé
        }

        $executed = RateLimiter::attempt(
            key: self::RATE_LIMIT_KEY,
            maxAttempts: $this->maxRequestsPerMinute,
            callback: static fn () => true,
            decaySeconds: 60,
        );

        if (!$executed) {
            $availableIn = RateLimiter::availableIn(self::RATE_LIMIT_KEY);

            throw new SgmefAuthenticationException(
                "Limite de taux outgoing atteinte ({$this->maxRequestsPerMinute} req/min). "
                . "Réessayez dans {$availableIn}s.",
                0,
                null,
                null,
                429,
                'RATE_LIMIT_OUTGOING',
                [
                    'guard' => 'rate_limit',
                    'max_per_minute' => $this->maxRequestsPerMinute,
                    'retry_after' => $availableIn,
                ]
            );
        }
    }
}
