<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\Services\Taxes\FixedRateTaxStrategy;
use InvalidArgumentException;

class TaxCalculatorService
{
    /** @var array<string, FixedRateTaxStrategy> */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = collect([
            new FixedRateTaxStrategy('A', 0.0),
            new FixedRateTaxStrategy('B', 0.18),
            new FixedRateTaxStrategy('C', 0.18, 0.01),
            new FixedRateTaxStrategy('D', 0.18, 0.05),
            new FixedRateTaxStrategy('E', 0.0),
            new FixedRateTaxStrategy('F', 0.0),
        ])->keyBy(fn (FixedRateTaxStrategy $strategy) => $strategy->group())->all();
    }

    /**
     * @return array{base: float, tax: float, aib: float, total: float}
     */
    public function calculateItem(InvoiceItemDto $item): array
    {
        $strategy = $this->strategies[$item->taxGroup] ?? null;

        if (!$strategy) {
            throw new InvalidArgumentException("Groupe de taxe inconnu: {$item->taxGroup}");
        }

        return $strategy->calculate($item);
    }

    /**
     * @param iterable<InvoiceItemDto> $items
     *
     * @return array{base: float, tax: float, aib: float, total: float, by_group: array<string, array{base: float, tax: float, aib: float, total: float}>}
     */
    public function calculateInvoice(iterable $items): array
    {
        $summary = ['base' => 0.0, 'tax' => 0.0, 'aib' => 0.0, 'total' => 0.0, 'by_group' => []];

        foreach ($items as $item) {
            $line = $this->calculateItem($item);
            $group = $item->taxGroup;

            $summary['by_group'][$group] ??= ['base' => 0.0, 'tax' => 0.0, 'aib' => 0.0, 'total' => 0.0];

            foreach (['base', 'tax', 'aib', 'total'] as $key) {
                $summary[$key] += $line[$key];
                $summary['by_group'][$group][$key] += $line[$key];
            }
        }

        return $summary;
    }
}
