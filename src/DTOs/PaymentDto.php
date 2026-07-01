<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\Validation\GreaterThan;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class PaymentDto extends Data
{
    public function __construct(
        #[Required]
        #[In(['ESPECES', 'VIREMENT', 'CARTEBANCAIRE', 'MOBILEMONEY', 'CHEQUES', 'CREDIT', 'AUTRE'])]
        public readonly string $name,
        #[Required]
        #[GreaterThan(0)]
        public readonly float $amount
    ) {
    }
}
