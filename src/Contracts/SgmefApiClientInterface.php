<?php

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;

/**
 * Interface pour le client API SyGM-eMCF
 */
interface SgmefApiClientInterface
{
    /**
     * Vérifie le statut de l'API
     */
        public function getStatus(): ApiStatusDto;

    /**
     * Récupère la liste des groupes de taxes
     */
        /** @return \Banelsems\LaraSgmefQr\DTOs\TaxGroupDto[] */
    public function getTaxGroups(): array;

    /**
     * Récupère la liste des types de factures
     */
        /** @return \Banelsems\LaraSgmefQr\DTOs\InvoiceTypeDto[] */
    public function getInvoiceTypes(): array;

    /**
     * Récupère la liste des types de paiement
     */
        /** @return \Banelsems\LaraSgmefQr\DTOs\PaymentTypeDto[] */
    public function getPaymentTypes(): array;

    /**
     * Crée une nouvelle facture
     */
    public function createInvoice(InvoiceRequestDto $invoiceData): InvoiceResponseDto;

    /**
     * Récupère les détails d'une facture par son UID
     */
    public function getInvoice(string $uid): InvoiceResponseDto;

    /**
     * Confirme une facture et récupère les éléments de sécurité
     */
        public function confirmInvoice(string $uid, bool $withQrCode = false): array;

    /**
     * Annule une facture
     */
        public function cancelInvoice(string $uid): array;

    /**
     * Configure les credentials de l'API
     */
    public function setCredentials(string $token): void;

    /**
     * Configure l'URL de base de l'API
     */
    public function setBaseUrl(string $baseUrl): void;
}
