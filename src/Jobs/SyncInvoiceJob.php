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
 * Job async de synchronisation d'une facture avec l'API e-MECeF.
 *
 * GET /invoice/{uid} : safe-to-retry. C'est le meilleur candidat pour
 * retry automatique avec backoff.
 *
 * tries et backoff sont configurables via config('lara_sgmef_qr.queue').
 * ShouldBeUnique : une seule sync par invoice à la fois.
 */
class SyncInvoiceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 300;

    /** @var list<int> */
    public array $backoff = [5, 15];

    public function __construct(
        public readonly string $uid
    ) {
        $this->onQueue(config('lara_sgmef_qr.queue.queue', 'sgmef'));
        $this->tries = (int) config('lara_sgmef_qr.queue.tries.sync', 3);
        $this->backoff = config('lara_sgmef_qr.queue.backoff.sync', [5, 15]);
    }

    public function uniqueId(): string
    {
        return 'sync:' . $this->uid;
    }

    public function handle(InvoiceManagerInterface $manager): ?Invoice
    {
        try {
            return $manager->syncInvoice($this->uid);
        } catch (InvoiceException $e) {
            Log::warning('SyncInvoiceJob: erreur', [
                'uid' => $this->uid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncInvoiceJob: échec définitif', [
            'uid' => $this->uid,
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
