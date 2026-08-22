<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Services\SgmefApiClient;
use Illuminate\Http\Client\Factory as HttpClient;

/**
 * Factory centralisée pour construire des instances de SgmefApiClient.
 *
 * Chaque appel à make() produit une nouvelle instance isolée, construite
 * depuis une SgmefConnectionConfig immutable. Aucune mutation d'un
 * singleton partagé — safe pour Octane, queue workers, tests.
 */
class SgmefApiClientFactory
{
    public function __construct(
        private readonly HttpClient $httpClient
    ) {
    }

    /**
     * Crée une nouvelle instance isolée de SgmefApiClient.
     *
     * Le client construit est immutable après instanciation : ses setters
     * (setBaseUrl/setCredentials) sont dépréciés et ne doivent plus être
     * appelés. Toute variation de configuration doit passer par une
     * nouvelle SgmefConnectionConfig + un nouvel appel à make().
     */
    public function make(SgmefConnectionConfig $config): SgmefApiClientInterface
    {
        return new SgmefApiClient(
            $this->httpClient,
            $config->baseUrl,
            $config->token,
            $config->httpOptions()
        );
    }

    /**
     * Crée une instance depuis la configuration courante du package.
     */
    public function makeFromConfig(): SgmefApiClientInterface
    {
        return $this->make(
            SgmefConnectionConfig::fromConfig(config('lara_sgmef_qr', []))
        );
    }
}
