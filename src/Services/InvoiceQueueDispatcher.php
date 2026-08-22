<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Jobs\CancelInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\ConfirmInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\CreateInvoiceJob;
use Banelsems\LaraSgmefQr\Jobs\SyncInvoiceJob;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Bus\PendingDispatch;

/**
 * API async additive pour la facturation e-MECeF.
 *
 * Les Jobs appellent les mêmes services métier (InvoiceManager) que l'API
 * synchrone. Le dispatcher ne duplique aucune logique métier.
 *
 * L'IFU et l'operator sont capturés dans le payload du Job au moment du
 * dispatch pour éviter toute fuite de contexte request dans le worker.
 *
 * Toutes les méthodes dispatchent sur la queue configurée
 * (config('lara_sgmef_qr.queue.queue', 'sgmef')) et utilisent afterCommit()
 * pour garantir que le job n'est envoyé qu'après commit de la transaction
 * DB courante (si active).
 */
final class InvoiceQueueDispatcher
{
    /**
     * Dispatche un job de création de facture.
     *
     * Le DTO est validé puis converti en array au moment du dispatch.
     * L'IFU et l'operator sont figés dans le payload.
     */
    public function create(InvoiceRequestDto $dto): PendingDispatch
    {
        return CreateInvoiceJob::dispatch($dto->toArray())->afterCommit();
    }

    /**
     * Dispatche un job de confirmation de facture.
     */
    public function confirm(Invoice $invoice): PendingDispatch
    {
        return ConfirmInvoiceJob::dispatch($invoice->requireUid())->afterCommit();
    }

    /**
     * Dispatche un job de confirmation par UID.
     */
    public function confirmByUid(string $uid): PendingDispatch
    {
        return ConfirmInvoiceJob::dispatch($uid)->afterCommit();
    }

    /**
     * Dispatche un job d'annulation de facture.
     */
    public function cancel(Invoice $invoice): PendingDispatch
    {
        return CancelInvoiceJob::dispatch($invoice->requireUid())->afterCommit();
    }

    /**
     * Dispatche un job d'annulation par UID.
     */
    public function cancelByUid(string $uid): PendingDispatch
    {
        return CancelInvoiceJob::dispatch($uid)->afterCommit();
    }

    /**
     * Dispatche un job de synchronisation de facture.
     */
    public function sync(Invoice $invoice): PendingDispatch
    {
        return SyncInvoiceJob::dispatch($invoice->requireUid())->afterCommit();
    }

    /**
     * Dispatche un job de synchronisation par UID.
     */
    public function syncByUid(string $uid): PendingDispatch
    {
        return SyncInvoiceJob::dispatch($uid)->afterCommit();
    }
}
