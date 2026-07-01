<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Carbon\CarbonInterface;
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
            'by_type' => $invoices->groupBy('type')->map->count()->all(),
            'by_tax_group' => $this->taxGroups($invoices),
        ];
    }

    public function exportCsv(CarbonInterface $from, CarbonInterface $to, ?string $path = null): string
    {
        $path ??= storage_path('app/lara_sgmef_qr/stat_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv');
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'wb');
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

        fclose($handle);

        return $path;
    }

    private function query(CarbonInterface $from, CarbonInterface $to)
    {
        return Invoice::query()
            ->where('status', InvoiceStatusEnum::CONFIRMED)
            ->whereBetween('confirmed_at', [$from->startOfDay(), $to->endOfDay()]);
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
