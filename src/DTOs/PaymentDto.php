<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Banelsems\LaraSgmefQr\Enums\PaymentTypeEnum;
use Banelsems\LaraSgmefQr\Support\Money;
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

    /**
     * Retourne le type de paiement typé.
     */
    public function type(): PaymentTypeEnum
    {
        return PaymentTypeEnum::from($this->name);
    }

    /**
     * Retourne le montant en XOF (entier) via le value object Money.
     */
    public function amountMoney(): Money
    {
        return Money::from($this->amount);
    }
}

