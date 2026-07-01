<?php

declare(strict_types=1);

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
        public readonly ?string $ifu = null,
        #[Required]
        public readonly ?string $name = null,
        public readonly ?string $contact = null,
        public readonly ?string $address = null
    ) {
    }
}
