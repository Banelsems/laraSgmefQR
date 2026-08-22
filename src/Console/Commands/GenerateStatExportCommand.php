<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Console\Commands;

use Banelsems\LaraSgmefQr\Services\StatExportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateStatExportCommand extends Command
{
    protected $signature = 'emecef:export-stat {--from=} {--to=} {--path=}';

    protected $description = 'Génère un export STAT CSV des factures e-MECeF confirmées.';

    public function handle(StatExportService $exports): int
    {
        $fromOption = $this->option('from');
        $toOption = $this->option('to');

        $from = CarbonImmutable::parse(is_string($fromOption) ? $fromOption : now()->startOfMonth());
        $to = CarbonImmutable::parse(is_string($toOption) ? $toOption : now()->endOfMonth());

        $pathOption = $this->option('path');
        $path = $exports->exportCsv($from, $to, is_string($pathOption) ? $pathOption : null);
        $summary = $exports->summary($from, $to);

        $this->info('Export STAT généré: ' . $path);
        $this->line('Factures: ' . $summary['invoice_count']);
        $this->line('Montant total: ' . number_format($summary['total_amount'], 2, '.', ' '));

        return self::SUCCESS;
    }
}
