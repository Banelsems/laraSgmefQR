<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\Validation\Gt;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class InvoiceItemDto extends Data
{
    public function __construct(
        #[Required]
        public readonly string $name,

        #[Required]
        #[Gt(0)]
        public readonly float $price,

        #[Required]
        #[Gt(0)]
        public readonly int $quantity,

        #[Required]
        #[In(['A', 'B', 'C', 'D', 'E', 'F'])]
        public readonly string $taxGroup,

        public readonly ?string $code = null,

        public readonly ?float $originalPrice = null,

        public readonly ?string $priceModification = null
    ) {}
}
