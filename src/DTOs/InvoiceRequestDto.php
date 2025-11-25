<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO principal pour la requête de création de facture
 */
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class InvoiceRequestDto extends Data
{
    public function __construct(
        #[Required]
        #[Regex('/^\d{13}$/')]
        public readonly string $ifu,

        #[Required]
        #[In(['FV', 'FA', 'EV', 'EA'])]
        public readonly string $type,

        #[Required]
        #[Min(1)]
        #[DataCollectionOf(InvoiceItemDto::class)]
        public readonly DataCollection $items,

        public readonly ClientDto $client,

        public readonly OperatorDto $operator,

        #[Required]
        #[Min(1)]
        #[DataCollectionOf(PaymentDto::class)]
        public readonly DataCollection $payment,

        #[In(['A', 'B'])]
        public readonly ?string $aib = null,

        public readonly ?string $reference = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ifu: $data['ifu'],
            type: $data['type'],
            items: InvoiceItemDto::collection($data['items']),
            client: ClientDto::from($data['client']),
            operator: OperatorDto::from($data['operator']),
            payment: PaymentDto::collection($data['payment']),
            aib: $data['aib'] ?? null,
            reference: $data['reference'] ?? null
        );
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            $totalAmount = collect($data['items'] ?? [])->sum(function ($item) {
                return ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
            });

            $totalPayment = collect($data['payment'] ?? [])->sum('amount');

            if (abs($totalAmount - $totalPayment) > 0.01) {
                $validator->errors()->add(
                    'payment',
                    "Le montant total des paiements ({$totalPayment}) ne correspond pas au montant total de la facture ({$totalAmount})"
                );
            }
        });
    }
}
