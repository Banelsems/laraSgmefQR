<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ClientDto extends Data
{
    public function __construct(
        #[Nullable]
        #[Regex('/^\d{13}$/')]
        public readonly ?string $ifu,

        #[Required]
        public readonly ?string $name,

        public readonly ?string $contact,
        
        public readonly ?string $address
    ) {}
}
