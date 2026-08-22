<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Jobs;

use Banelsems\LaraSgmefQr\Services\ExpirePendingInvoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job de cleanup : marque les factures pending expirées au statut EXPIRED.
 *
 * Délègue au service ExpirePendingInvoices pour éviter la duplication
 * de logique entre la commande, le job et le scheduler.
 *
 * ShouldBeUnique : évite deux cleanups simultanés.
 */
class CleanupExpiredPendingInvoicesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public int $uniqueFor = 300;

    public function __construct(
        public readonly int $limit = 500
    ) {
        $this->onQueue(config('lara_sgmef_qr.queue.queue', 'sgmef'));
    }

    public function uniqueId(): string
    {
        return 'cleanup-expired';
    }

    public function handle(ExpirePendingInvoices $service): array
    {
        return $service->execute($this->limit, false);
    }
}
