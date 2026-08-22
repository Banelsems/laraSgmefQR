<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;

interface TaxCalculationStrategyInterface
{
    public function group(): string;

    /**
     * @return array{base: int, tax: int, aib: int, total: int}
     */
    public function calculate(InvoiceItemDto $item): array;
}
