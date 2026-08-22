<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Banelsems\LaraSgmefQr\Enums\InvoiceTypeEnum;
use Banelsems\LaraSgmefQr\Enums\PaymentTypeEnum;
use Banelsems\LaraSgmefQr\Support\Money;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * DTO principal pour la requête de création de facture.
 *
 * Source de vérité unique pour la validation des invariants métier :
 * IFU, type, référence FA/EA, items, paiements, cohérence totaux.
 * Le FormRequest InvoiceRequest ne gère que les préoccupations HTTP.
 */
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

    /**
     * Factory depuis un tableau.
     *
     * Construit le DTO en validant les invariants métier via les attributs
     * PHP 8 et withValidator(). La validation des totaux (items vs paiements)
     * et de la référence FA/EA est centralisée ici, évitant la duplication
     * qui existait entre FormRequest, fromArray() et InvoiceManager.
     */
    public static function fromArray(array $data): self
    {
        // Valider les invariants métier avant construction
        $validator = ValidatorFacade::make($data, self::rules());

        $validator->after(function (Validator $validator) use ($data): void {
            $totalItems = self::sumItemsTotal($data['items'] ?? []);
            $totalPayment = self::sumPayments($data['payment'] ?? []);

            if ($totalItems !== $totalPayment) {
                $validator->errors()->add(
                    'payment',
                    "Le montant total des paiements ({$totalPayment}) ne correspond pas au montant total de la facture ({$totalItems})."
                );
            }
        });

        $validator->validate();

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

    /**
     * Règles de validation centralisées pour les invariants métier.
     *
     * @return array<string, mixed>
     */
    private static function rules(): array
    {
        return [
            'ifu' => ['required', 'regex:/^\d{13}$/'],
            'type' => ['required', 'in:FV,FA,EV,EA'],
            'client' => ['required', 'array'],
            'client.name' => ['required', 'string'],
            'client.ifu' => ['nullable', 'regex:/^\d{13}$/'],
            'operator' => ['nullable', 'array'],
            'operator.id' => ['nullable'],
            'operator.name' => ['nullable', 'string'],
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
        ];
    }

    /**
     * Calcule le total des items en XOF (entier) depuis les données brutes.
     *
     * @param  mixed  $items
     */
    private static function sumItemsTotal(mixed $items): int
    {
        if (!is_array($items) && !($items instanceof \Traversable)) {
            return 0;
        }

        $total = 0;

        foreach ($items as $item) {
            $price = is_array($item) ? ($item['price'] ?? 0) : (is_object($item) ? ($item->price ?? 0) : 0);
            $quantity = is_array($item) ? ($item['quantity'] ?? 1) : (is_object($item) ? ($item->quantity ?? 1) : 1);
            $total += Money::from($price)->multiply((float) $quantity)->getAmount();
        }

        return $total;
    }

    /**
     * Calcule le total des paiements en XOF (entier) depuis les données brutes.
     *
     * @param  mixed  $payments
     */
    private static function sumPayments(mixed $payments): int
    {
        if (!is_array($payments) && !($payments instanceof \Traversable)) {
            return 0;
        }

        $total = 0;

        foreach ($payments as $payment) {
            $amount = is_array($payment) ? ($payment['amount'] ?? 0) : (is_object($payment) ? ($payment->amount ?? 0) : 0);
            $total += Money::from($amount)->getAmount();
        }

        return $total;
    }
}
