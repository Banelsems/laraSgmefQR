<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\Validation\Gt;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class PaymentDto extends Data
{
    public function __construct(
        #[Required]
        public readonly string $name,

        #[Required]
        #[Gt(0)]
        public readonly float $amount
    ) {}
}
