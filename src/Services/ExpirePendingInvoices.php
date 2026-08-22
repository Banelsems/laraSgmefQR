<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Events\InvoiceExpired;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Marque les factures pending expirées (expires_at <= now) au statut EXPIRED.
 *
 * Source de vérité : la colonne expires_at (calculée à la création selon
 * la règle e-MECeF). Ne recalcule pas created_at + 2 minutes.
 *
 * Le traitement est limité par $limit et dispatche InvoiceExpired
 * pour chaque facture réellement expirée. En dry-run, aucune modification
 * n'est effectuée et aucun événement n'est dispatché.
 */
final class ExpirePendingInvoices
{
    /**
     * @return array{expired: int, skipped: int} Nombre de factures expirées et ignorées.
     */
    public function execute(int $limit = 500, bool $dryRun = false): array
    {
        $expired = 0;
        $skipped = 0;

        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices */
        $invoices = Invoice::query()
            ->where('status', InvoiceStatusEnum::PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($invoices as $invoice) {
            if ($dryRun) {
                $skipped++;
                continue;
            }

            $invoice->update([
                'status' => InvoiceStatusEnum::EXPIRED,
                'error_code' => 'EXPIRED_PENDING',
                'error_description' => 'Facture non confirmée dans le délai e-MECeF (expires_at dépassé).',
            ]);

            Event::dispatch(new InvoiceExpired($invoice));
            $expired++;
        }

        if ($expired > 0 || $dryRun) {
            Log::info('ExpirePendingInvoices: traitement terminé', [
                'expired' => $expired,
                'skipped' => $skipped,
                'dry_run' => $dryRun,
            ]);
        }

        return ['expired' => $expired, 'skipped' => $skipped];
    }
}
