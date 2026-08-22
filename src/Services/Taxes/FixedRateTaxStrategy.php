<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services\Taxes;

use Banelsems\LaraSgmefQr\Contracts\TaxCalculationStrategyInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\Support\Money;

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

    /**
     * @return array{base: int, tax: int, aib: int, total: int}
     */
    public function calculate(InvoiceItemDto $item): array
    {
        $base = Money::from($item->price)->multiply((float) $item->quantity);
        $tax = (int) round($base->getAmount() * $this->vatRate);
        $aib = (int) round($base->getAmount() * $this->aibRate);
        $specific = Money::from($item->taxSpecific ?? 0)->getAmount();

        return [
            'base' => $base->getAmount(),
            'tax' => $tax + $specific,
            'aib' => $aib,
            'total' => $base->getAmount() + $tax + $aib + $specific,
        ];
    }
}
