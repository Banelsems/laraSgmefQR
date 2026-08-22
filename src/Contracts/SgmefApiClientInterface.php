<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;

/**
 * Interface pour le client API SyGM-eMCF
 */
interface SgmefApiClientInterface
{
    /**
     * Vérifie le statut de l'API.
     */
    public function getStatus(): ApiStatusDto;

    /**
     * Récupère la liste des groupes de taxes.
     *
     * @return \Banelsems\LaraSgmefQr\DTOs\TaxGroupDto[]
     */
    public function getTaxGroups(): array;

    /**
     * Récupère la liste des types de factures.
     *
     * @return \Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto[]
     */
    public function getInvoiceTypes(): array;

    /**
     * Récupère la liste des types de paiement.
     *
     * @return \Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto[]
     */
    public function getPaymentTypes(): array;

    /**
     * Crée une nouvelle facture.
     */
    public function createInvoice(InvoiceRequestDto $invoiceData): InvoiceResponseDto;

    /**
     * Récupère les détails d'une facture par son UID.
     */
    public function getInvoice(string $uid): InvoiceResponseDto;

    /**
     * Confirme une facture et récupère les éléments de sécurité.
     *
     * Le paramètre $withQrCode est conservé pour rétrocompatibilité mais
     * n'a actuellement aucun effet : l'API e-MECeF retourne toujours le QR
     * code lors de la confirmation. Ce paramètre sera supprimé en v3.2.
     *
     * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
     */
    public function confirmInvoice(string $uid, bool $withQrCode = false): SecurityElementsDto;

    /**
     * Annule une facture.
     */
    public function cancelInvoice(string $uid): array;

    /**
     * Configure les credentials de l'API.
     *
     * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
     *             Utiliser SgmefApiClientFactory::make() avec une
     *             SgmefConnectionConfig immutable à la place.
     *             La mutation d'un client partagé est dangereuse en
     *             contexte long-lived (Octane, queue, Swoole).
     */
    public function setCredentials(string $token): void;

    /**
     * Configure l'URL de base de l'API.
     *
     * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
     *             Utiliser SgmefApiClientFactory::make() avec une
     *             SgmefConnectionConfig immutable à la place.
     *             La mutation d'un client partagé est dangereuse en
     *             contexte long-lived (Octane, queue, Swoole).
     */
    public function setBaseUrl(string $baseUrl): void;
}
