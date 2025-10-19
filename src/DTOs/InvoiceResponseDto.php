<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class InvoiceResponseDto extends Data
{
    public function __construct(
        public readonly string $uid,

        #[MapInputName('total')]
        public readonly float $totalAmount,

        #[MapInputName('ts')]
        public readonly ?float $totalTaxAmount,

        #[MapInputName('aib')]
        public readonly ?float $totalAibAmount,

        public readonly ?array $items,

        public readonly ?string $status,

        #[MapInputName('date')]
        public readonly ?string $dateTime
    ) {}
}
