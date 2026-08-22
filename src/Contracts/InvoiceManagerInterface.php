<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Models\Invoice;

interface InvoiceManagerInterface
{
    /**
     * Crée une nouvelle facture auprès de l'API e-MECeF et la persiste localement.
     */
    public function createInvoice(InvoiceRequestDto $invoiceData): Invoice;

    /**
     * Confirme une facture pending et récupère les éléments de sécurité.
     */
    public function confirmInvoice(string $uid): Invoice;

    /**
     * Annule une facture pending ou confirmée via PUT /invoice/{uid}/cancel.
     */
    public function cancelInvoice(string $uid): Invoice;

    /**
     * Annule une facture via POST /invoice/cancel (méthode alternative body-based).
     */
    public function cancelInvoiceByPost(string $uid): Invoice;

    /**
     * Récupère une facture par son UID local.
     */
    public function getInvoice(string $uid): Invoice;

    /**
     * Synchronise une facture avec l'API e-MECeF (récupère l'état distant).
     */
    public function syncInvoice(string $uid): Invoice;
}
