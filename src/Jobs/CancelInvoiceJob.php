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
 * Job async d'annulation de facture.
 *
 * PUT /invoice/{uid}/cancel : non retryé par défaut (idempotence non
 * documentée). tries = 1.
 *
 * ShouldBeUnique avec une clé distincte de confirm pour éviter qu'un
 * cancel et un confirm concurrent se bloquent via la même clé.
 * La protection contre la race confirm/cancel repose sur les préconditions
 * canBeConfirmed()/canBeCancelled() dans InvoiceManager.
 */
class CancelInvoiceJob implements ShouldQueue, ShouldBeUnique
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
        return 'cancel:' . $this->uid;
    }

    public function handle(InvoiceManagerInterface $manager): ?Invoice
    {
        try {
            return $manager->cancelInvoice($this->uid);
        } catch (InvoiceException $e) {
            Log::warning('CancelInvoiceJob: erreur', [
                'uid' => $this->uid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CancelInvoiceJob: échec définitif', [
            'uid' => $this->uid,
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
