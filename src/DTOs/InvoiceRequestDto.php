<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO principal pour la requête de création de facture
 */
use Illuminate\Support\Facades\Validator as ValidatorFacade;
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
        public readonly DataCollection|array $items,
        public readonly ClientDto $client,
        public readonly OperatorDto $operator,
        #[Required]
        #[Min(1)]
        #[DataCollectionOf(PaymentDto::class)]
        public readonly DataCollection|array $payment,
        #[In(['A', 'B'])]
        public readonly ?string $aib = null,
        public readonly ?string $reference = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        ValidatorFacade::make($data, [
            'ifu' => ['required', 'regex:/^\d{13}$/'],
            'type' => ['required', 'in:FV,FA,EV,EA'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'gt:0'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.taxGroup' => ['required', 'in:A,B,C,D,E,F'],
            'items.*.taxSpecific' => ['nullable', 'numeric', 'gte:0'],
            'items.*.originalPrice' => ['nullable', 'numeric', 'gt:0'],
            'payment' => ['required', 'array', 'min:1'],
            'payment.*.name' => ['required', 'in:ESPECES,VIREMENT,CARTEBANCAIRE,MOBILEMONEY,CHEQUES,CREDIT,AUTRE'],
            'payment.*.amount' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'required_if:type,FA,EA', 'string'],
        ])->after(function (Validator $validator) use ($data): void {
            $totalAmount = collect($data['items'] ?? [])->sum(fn ($item) => (float) ($item['price'] ?? 0) * (float) ($item['quantity'] ?? 1));
            $totalPayment = collect($data['payment'] ?? [])->sum('amount');

            if (abs($totalAmount - $totalPayment) > 0.01) {
                $validator->errors()->add('payment', 'Le montant total des paiements ne correspond pas au montant total de la facture.');
            }
        })->validate();

        return new self(
            ifu: $data['ifu'],
            type: $data['type'],
            items: new DataCollection(InvoiceItemDto::class, $data['items']),
            client: ClientDto::from($data['client']),
            operator: OperatorDto::from($data['operator'] ?? config('lara_sgmef_qr.default_operator', ['id' => '1', 'name' => 'Opérateur Principal'])),
            payment: new DataCollection(PaymentDto::class, $data['payment']),
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

            if (in_array($data['type'] ?? null, ['FA', 'EA'], true) && empty($data['reference'])) {
                $validator->errors()->add(
                    'reference',
                    'La référence de la facture originale est obligatoire pour les factures d’avoir FA/EA.'
                );
            }
        });
    }
}
