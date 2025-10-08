<?php

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;

/**
 * Interface pour le client API SyGM-eMCF
 */
interface SgmefApiClientInterface
{
    /**
     * Vérifie le statut de l'API
     */
    public function getStatus(): array;

    /**
     * Récupère la liste des groupes de taxes
     */
    public function getTaxGroups(): array;

    /**
     * Récupère la liste des types de factures
     */
    public function getInvoiceTypes(): array;

    /**
     * Récupère la liste des types de paiement
     */
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
    public function confirmInvoice(string $uid): SecurityElementsDto;

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
