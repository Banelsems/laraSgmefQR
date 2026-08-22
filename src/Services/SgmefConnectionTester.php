<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefApiClientFactory;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;

/**
 * Testeur de connexion à l'API e-MECeF.
 *
 * Construit une instance isolée de SgmefApiClient via la factory,
 * sans muter le singleton partagé. Safe pour Octane, queue, tests.
 */
class SgmefConnectionTester
{
    public function __construct(
        private readonly SgmefApiClientFactory $factory
    ) {
    }

    /**
     * Teste la connexion avec des credentials potentiels.
     *
     * @param  string  $apiUrl  URL de l'API à tester.
     * @param  string|null  $token  Token à tester (null = utiliser le token courant).
     */
    public function test(string $apiUrl, ?string $token = null): ApiStatusDto
    {
        $config = SgmefConnectionConfig::fromValues(
            baseUrl: $apiUrl,
            token: $token
        );

        $client = $this->factory->make($config);

        return $client->getStatus();
    }
}
