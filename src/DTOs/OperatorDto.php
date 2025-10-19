<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class OperatorDto extends Data
{
    public function __construct(
        #[Required]
        public readonly int|string $id,

        #[Required]
        public readonly string $name
    ) {}
}
