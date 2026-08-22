<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

/**
 * Configuration immutable pour une connexion à l'API e-MECeF.
 *
 * Construite une fois, jamais modifiée. Permet de créer des instances
 * isolées de SgmefApiClient via la factory, sans muter un singleton partagé.
 *
 * Compatible PHP 8.1 : utilise readonly properties (pas readonly class).
 */
final class SgmefConnectionConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $token,
        public readonly int $timeout = 30,
        public readonly int $connectTimeout = 10,
        public readonly bool $verifySsl = true
    ) {
    }

    /**
     * Construit une configuration depuis le tableau de config du package.
     *
     * @param  array<string, mixed>  $config  Configuration LaraSgmefQR.
     */
    public static function fromConfig(array $config): self
    {
        $httpOptions = $config['http_options'] ?? [];

        return new self(
            baseUrl: (string) ($config['api_url'] ?? ''),
            token: (string) ($config['token'] ?? ''),
            timeout: (int) ($httpOptions['timeout'] ?? 30),
            connectTimeout: (int) ($httpOptions['connect_timeout'] ?? 10),
            verifySsl: (bool) ($httpOptions['verify'] ?? true)
        );
    }

    /**
     * Construit une configuration depuis des valeurs individuelles,
     * en fusionnant avec les valeurs par défaut du package.
     */
    public static function fromValues(
        string $baseUrl,
        ?string $token = null,
        ?int $timeout = null,
        ?int $connectTimeout = null,
        ?bool $verifySsl = null
    ): self {
        $config = config('lara_sgmef_qr', []);

        return new self(
            baseUrl: $baseUrl,
            token: $token ?? (string) ($config['token'] ?? ''),
            timeout: $timeout ?? (int) ($config['http_options']['timeout'] ?? 30),
            connectTimeout: $connectTimeout ?? (int) ($config['http_options']['connect_timeout'] ?? 10),
            verifySsl: $verifySsl ?? (bool) ($config['http_options']['verify'] ?? true)
        );
    }

    /**
     * Retourne les options HTTP au format attendu par le HttpClient de Laravel.
     *
     * @return array{timeout: int, connect_timeout: int, verify: bool}
     */
    public function httpOptions(): array
    {
        return [
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify' => $this->verifySsl,
        ];
    }
}
