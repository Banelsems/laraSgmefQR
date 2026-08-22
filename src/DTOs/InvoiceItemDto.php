<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Banelsems\LaraSgmefQr\Support\Money;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\GreaterThan;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class InvoiceItemDto extends Data
{
    public function __construct(
        #[Required]
        public readonly string $name,
        #[Required]
        #[GreaterThan(0)]
        public readonly float $price,
        #[Required]
        #[GreaterThan(0)]
        public readonly float $quantity,
        #[Required]
        #[In(['A', 'B', 'C', 'D', 'E', 'F'])]
        public readonly string $taxGroup,
        public readonly ?string $code = null,
        #[GreaterThanOrEqualTo(0)]
        public readonly ?float $taxSpecific = null,
        #[GreaterThan(0)]
        public readonly ?float $originalPrice = null,
        public readonly ?string $priceModification = null
    ) {
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $data = $validator->getData();

            $price = (float) ($data['price'] ?? 0);
            $taxSpecific = $data['taxSpecific'] ?? null;
            $originalPrice = $data['originalPrice'] ?? null;
            $priceModification = $data['priceModification'] ?? null;

            if ($taxSpecific !== null && (float) $taxSpecific > $price) {
                $validator->errors()->add('taxSpecific', 'La taxe spécifique ne peut pas dépasser le prix unitaire.');
            }

            if (filled($priceModification) && $originalPrice === null) {
                $validator->errors()->add('originalPrice', 'Le prix original est requis lorsqu\'une modification de prix est renseignée.');
            }
        });
    }

    /**
     * Retourne le prix unitaire en XOF (entier) via le value object Money.
     */
    public function priceMoney(): Money
    {
        return Money::from($this->price);
    }

    /**
     * Retourne le total de ligne (prix × quantité) en XOF via Money.
     */
    public function lineTotal(): Money
    {
        return $this->priceMoney()->multiply((float) $this->quantity);
    }

    /**
     * Retourne la taxe spécifique en XOF (0 si absente).
     */
    public function taxSpecificMoney(): Money
    {
        return Money::from($this->taxSpecific ?? 0);
    }

    /**
     * Retourne le prix original en XOF (null si absent).
     */
    public function originalPriceMoney(): ?Money
    {
        return $this->originalPrice !== null ? Money::from($this->originalPrice) : null;
    }
}

