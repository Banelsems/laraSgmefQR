<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

/**
 * Sanitization récursive des données de log.
 *
 * Masque les valeurs des clés sensibles (tokens, passwords, secrets)
 * pour empêcher toute fuite dans les logs. La correspondance des clés
 * est insensible à la casse.
 */
final class SgmefLogSanitizer
{
    /**
     * Clés sensibles dont les valeurs doivent être masquées.
     * La correspondance est insensible à la casse.
     */
    private const SENSITIVE_KEYS = [
        'authorization',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'password',
        'secret',
        'credentials',
        'api_key',
        'apikey',
        'private_key',
        'bearer',
    ];

    private const REDACTED_VALUE = '[REDACTED]';

    /**
     * Sanitize un tableau récursivement en masquant les valeurs sensibles.
     *
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    public function sanitize(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $result[$key] = self::REDACTED_VALUE;
            } elseif (is_array($value)) {
                $result[$key] = $this->sanitize($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Vérifie si une clé correspond à une clé sensible (insensible à la casse).
     */
    private function isSensitiveKey(string|int $key): bool
    {
        if (!is_string($key)) {
            return false;
        }

        $lowerKey = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if ($lowerKey === $sensitive) {
                return true;
            }
            // Correspondance partielle : la clé contient un terme sensible
            // (ex: "x-authorization", "my_token", "user_password")
            if (str_contains($lowerKey, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
