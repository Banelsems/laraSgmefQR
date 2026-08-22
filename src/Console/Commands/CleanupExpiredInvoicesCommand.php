<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Console\Commands;

use Banelsems\LaraSgmefQr\Services\ExpirePendingInvoices;
use Illuminate\Console\Command;

/**
 * Marque les factures pending expirées (expires_at <= now) au statut EXPIRED.
 *
 * Usage :
 *   php artisan sgmef:cleanup-expired
 *   php artisan sgmef:cleanup-expired --dry-run
 *   php artisan sgmef:cleanup-expired --limit=1000
 */
class CleanupExpiredInvoicesCommand extends Command
{
    protected $signature = 'sgmef:cleanup-expired
                            {--dry-run : Identifier sans modifier}
                            {--limit=500 : Nombre maximum de factures par batch}';

    protected $description = 'Marque les factures pending expirées (expires_at <= now) au statut EXPIRED.';

    public function handle(ExpirePendingInvoices $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));

        if ($dryRun) {
            $this->info('Mode dry-run : aucune facture ne sera modifiée.');
        }

        $result = $service->execute($limit, $dryRun);

        if ($dryRun) {
            $this->info("{$result['skipped']} facture(s) pending expirée(s) identifiée(s) (non modifiées).");
        } else {
            $this->info("{$result['expired']} facture(s) marquée(s) EXPIRED.");
        }

        return self::SUCCESS;
    }
}
