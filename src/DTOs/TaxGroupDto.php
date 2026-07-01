<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class TaxGroupDto extends Data
{
    public function __construct(
        public string $code,
        public ?string $name,
        public float $rate
    ) {
    }
}
