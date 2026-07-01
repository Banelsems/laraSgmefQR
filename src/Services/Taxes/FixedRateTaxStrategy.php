<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services\Taxes;

use Banelsems\LaraSgmefQr\Contracts\TaxCalculationStrategyInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;

class FixedRateTaxStrategy implements TaxCalculationStrategyInterface
{
    public function __construct(
        private readonly string $group,
        private readonly float $vatRate,
        private readonly float $aibRate = 0.0
    ) {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function calculate(InvoiceItemDto $item): array
    {
        $base = $item->price * $item->quantity;
        $tax = round($base * $this->vatRate, 2);
        $aib = round($base * $this->aibRate, 2);
        $specific = (float) ($item->taxSpecific ?? 0);

        return [
            'base' => $base,
            'tax' => $tax + $specific,
            'aib' => $aib,
            'total' => $base + $tax + $aib + $specific,
        ];
    }
}
