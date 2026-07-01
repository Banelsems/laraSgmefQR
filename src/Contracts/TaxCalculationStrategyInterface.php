<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;

interface TaxCalculationStrategyInterface
{
    public function group(): string;

    /**
     * @return array{base: float, tax: float, aib: float, total: float}
     */
    public function calculate(InvoiceItemDto $item): array;
}
