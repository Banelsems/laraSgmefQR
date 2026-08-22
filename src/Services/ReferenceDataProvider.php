<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProviderInterface;
use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProvenance;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto;
use Banelsems\LaraSgmefQr\DTOs\TaxGroupDto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Provider de référentiels e-MECeF avec cache et fallback explicite.
 *
 * Flux de récupération :
 * 1. Cache (si valide) → provenance = cache
 * 2. API e-MECeF → mise en cache → provenance = api
 * 3. Fallback configuré → provenance = fallback (avec log warning)
 * 4. Aucun fallback → exception explicite
 *
 * Le fallback n'avale jamais silencieusement une panne API : chaque
 * utilisation du fallback est loggée avec un warning.
 */
class ReferenceDataProvider implements ReferenceDataProviderInterface
{
    private ReferenceDataProvenance $lastProvenance = ReferenceDataProvenance::API;

    public function __construct(
        private readonly SgmefApiClientInterface $apiClient,
        private readonly int $cacheTtl = 3600,
        private readonly string $cachePrefix = 'lara_sgmef_qr',
        private readonly array $fallback = []
    ) {
    }

    /**
     * @return array<TaxGroupDto>
     */
    public function getTaxGroups(): array
    {
        return $this->fetch(
            'tax_groups',
            fn () => $this->apiClient->getTaxGroups(),
            $this->buildTaxGroupFallback()
        );
    }

    /**
     * @return array<PaymentTypeDto>
     */
    public function getPaymentTypes(): array
    {
        return $this->fetch(
            'payment_types',
            fn () => $this->apiClient->getPaymentTypes(),
            $this->buildPaymentTypeFallback()
        );
    }

    /**
     * @return array<InvoiceTypeDto>
     */
    public function getInvoiceTypes(): array
    {
        return $this->fetch(
            'invoice_types',
            fn () => $this->apiClient->getInvoiceTypes(),
            $this->buildInvoiceTypeFallback()
        );
    }

    public function lastProvenance(): ReferenceDataProvenance
    {
        return $this->lastProvenance;
    }

    /**
     * Récupère un référentiel avec cache + fallback.
     *
     * @template T
     * @param  string  $key  Clé de cache
     * @param  callable(): array<T>  $apiCall
     * @param  array<T>  $fallback
     * @return array<T>
     */
    private function fetch(string $key, callable $apiCall, array $fallback): array
    {
        $cacheKey = $this->cachePrefix . ':ref:' . $key;

        // 1. Tentative depuis le cache
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && count($cached) > 0) {
            $this->lastProvenance = ReferenceDataProvenance::CACHE;

            return $cached;
        }

        // 2. Tentative depuis l'API
        try {
            $data = $apiCall();
            if (count($data) > 0) {
                Cache::put($cacheKey, $data, $this->cacheTtl);
                $this->lastProvenance = ReferenceDataProvenance::API;

                return $data;
            }
        } catch (\Throwable $e) {
            Log::warning('ReferenceDataProvider: API failure, using fallback', [
                'reference' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        // 3. Fallback
        if (count($fallback) > 0) {
            $this->lastProvenance = ReferenceDataProvenance::FALLBACK;
            Log::warning('ReferenceDataProvider: using fallback data', [
                'reference' => $key,
            ]);

            return $fallback;
        }

        // 4. Aucune donnée disponible
        throw new \RuntimeException(
            "Impossible de charger le référentiel '{$key}' : API indisponible et aucun fallback configuré."
        );
    }

    /**
     * @return array<TaxGroupDto>
     */
    private function buildTaxGroupFallback(): array
    {
        $fallbackRates = $this->fallback['tax_groups'] ?? [];

        $result = [];
        foreach ($fallbackRates as $code => $rate) {
            $result[] = TaxGroupDto::from([
                'code' => strtoupper((string) $code),
                'name' => strtoupper((string) $code),
                'rate' => (float) $rate,
            ]);
        }

        return $result;
    }

    /**
     * @return array<PaymentTypeDto>
     */
    private function buildPaymentTypeFallback(): array
    {
        $fallbackTypes = $this->fallback['payment_types'] ?? [];

        $result = [];
        foreach ($fallbackTypes as $type) {
            $result[] = PaymentTypeDto::from([
                'type' => $type,
                'description' => $type,
            ]);
        }

        return $result;
    }

    /**
     * @return array<InvoiceTypeDto>
     */
    private function buildInvoiceTypeFallback(): array
    {
        $fallbackTypes = $this->fallback['invoice_types'] ?? [];

        $result = [];
        foreach ($fallbackTypes as $type) {
            $result[] = InvoiceTypeDto::from([
                'type' => $type,
                'description' => $type,
            ]);
        }

        return $result;
    }
}
