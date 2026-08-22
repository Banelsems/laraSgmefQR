<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class StatExportService
{
    public function summary(CarbonInterface $from, CarbonInterface $to): array
    {
        $invoices = $this->query($from, $to)->get();

        return [
            'period' => [$from->toDateString(), $to->toDateString()],
            'invoice_count' => $invoices->count(),
            'total_amount' => (float) $invoices->sum('total_amount'),
            'by_type' => $invoices->groupBy('type')->map(fn ($group): int => $group->count())->all(),
            'by_tax_group' => $this->taxGroups($invoices),
        ];
    }

    public function exportCsv(CarbonInterface $from, CarbonInterface $to, ?string $path = null): string
    {
        $path ??= storage_path('app/lara_sgmef_qr/stat_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv');
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier d'export CSV : {$path}");
        }

        try {
            fputcsv($handle, ['uid', 'ifu', 'customer_ifu', 'type', 'status', 'total_amount', 'mecf_code', 'confirmed_at']);

            $this->query($from, $to)->orderBy('confirmed_at')->chunk(200, function ($invoices) use ($handle): void {
                foreach ($invoices as $invoice) {
                    fputcsv($handle, [
                        $invoice->uid,
                        $invoice->ifu,
                        $invoice->customer_ifu,
                        $invoice->type,
                        $invoice->status->value,
                        $invoice->total_amount,
                        $invoice->mecf_code,
                        $invoice->confirmed_at?->toDateTimeString(),
                    ]);
                }
            });
        } finally {
            fclose($handle);
        }

        return $path;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Invoice>
     */
    private function query(CarbonInterface $from, CarbonInterface $to)
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Invoice> $builder */
        $builder = Invoice::query()
            ->where('status', InvoiceStatusEnum::CONFIRMED)
            ->whereBetween('confirmed_at', [$from->startOfDay(), $to->endOfDay()]);

        return $builder;
    }

    private function taxGroups(Collection $invoices): array
    {
        $groups = [];

        foreach ($invoices as $invoice) {
            foreach (($invoice->raw_request['items'] ?? []) as $item) {
                $group = $item['taxGroup'] ?? 'unknown';
                $groups[$group] = ($groups[$group] ?? 0) + 1;
            }
        }

        ksort($groups);

        return $groups;
    }
}
