<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Jobs;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job async de confirmation de facture.
 *
 * PUT /invoice/{uid}/confirm : non retryé par défaut (idempotence non
 * documentée par l'API e-MECeF). tries = 1.
 *
 * ShouldBeUnique : une seule confirmation par invoice à la fois.
 * Le payload est un UID (string) — pas de sérialisation de Model.
 *
 * Préconditions vérifiées au handle() : invoice existe, statut PENDING,
 * non expirée.
 */
class ConfirmInvoiceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 60;

    public int $uniqueFor = 300;

    public function __construct(
        public readonly string $uid
    ) {
        $this->onQueue(config('lara_sgmef_qr.queue.queue', 'sgmef'));
    }

    public function uniqueId(): string
    {
        return 'confirm:' . $this->uid;
    }

    public function handle(InvoiceManagerInterface $manager): ?Invoice
    {
        try {
            return $manager->confirmInvoice($this->uid);
        } catch (InvoiceException $e) {
            Log::warning('ConfirmInvoiceJob: erreur', [
                'uid' => $this->uid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ConfirmInvoiceJob: échec définitif', [
            'uid' => $this->uid,
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
