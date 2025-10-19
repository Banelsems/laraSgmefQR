<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class PaymentTypeDto extends Data
{
    public function __construct(
        public string $code,
        public string $name
    ) {}
}
