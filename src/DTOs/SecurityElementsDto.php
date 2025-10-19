<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class SecurityElementsDto extends Data
{
    public function __construct(
        public readonly string $dateTime,
        public readonly string $qrCode,
        public readonly string $codeMECeFDGI,
        public readonly string $counters,
        public readonly string $nim,
        public readonly bool $refundWithAibPayment
    ) {}
}
