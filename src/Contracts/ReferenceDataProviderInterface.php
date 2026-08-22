<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto;
use Banelsems\LaraSgmefQr\DTOs\TaxGroupDto;

/**
 * Provider pour les référentiels e-MECeF (tax groups, payment types, invoice types).
 *
 * Responsabilités : charger depuis l'API, mettre en cache, fournir un
 * fallback explicite, signaler la provenance.
 */
interface ReferenceDataProviderInterface
{
    /**
     * @return array<TaxGroupDto>
     */
    public function getTaxGroups(): array;

    /**
     * @return array<PaymentTypeDto>
     */
    public function getPaymentTypes(): array;

    /**
     * @return array<InvoiceTypeDto>
     */
    public function getInvoiceTypes(): array;

    /**
     * Retourne la provenance de la dernière récupération.
     *
     * @return ReferenceDataProvenance
     */
    public function lastProvenance(): ReferenceDataProvenance;
}
