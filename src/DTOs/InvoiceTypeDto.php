<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class InvoiceTypeDto extends Data
{
    public function __construct(
        public string $type,
        public ?string $description = null
    ) {
    }
}
