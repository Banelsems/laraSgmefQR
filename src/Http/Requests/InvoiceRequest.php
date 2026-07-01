<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request pour la validation des données de facture
 */
class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ifu' => ['required', 'string', 'regex:/^\d{13}$/'],
            'type' => ['required', 'string', 'in:FV,FA,EV,EA'],
            'aib' => ['nullable', 'string', 'in:A,B'],
            'reference' => ['nullable', 'required_if:type,FA,EA', 'string', 'max:255'],

            // Client
            'client.name' => ['required', 'string', 'max:255'],
            'client.ifu' => ['nullable', 'string', 'regex:/^\d{13}$/'],
            'client.contact' => ['nullable', 'string', 'max:255'],
            'client.address' => ['nullable', 'string', 'max:500'],

            // Opérateur (optionnel - sera rempli automatiquement si vide)
            'operator.id' => ['nullable'],
            'operator.name' => ['nullable', 'string', 'max:255'],

            // Articles
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.taxGroup' => ['required', 'string', 'in:A,B,C,D,E,F'],
            'items.*.code' => ['nullable', 'string', 'max:100'],
            'items.*.taxSpecific' => ['nullable', 'numeric', 'gte:0'],
            'items.*.originalPrice' => ['nullable', 'numeric', 'gt:0'],
            'items.*.priceModification' => ['nullable', 'required_with:items.*.originalPrice', 'string', 'max:50'],

            // Paiements
            'payment' => ['required', 'array', 'min:1'],
            'payment.*.name' => ['required', 'string', 'in:ESPECES,VIREMENT,CARTEBANCAIRE,MOBILEMONEY,CHEQUES,CREDIT,AUTRE'],
            'payment.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ifu.required' => 'L\'IFU est requis',
            'ifu.regex' => 'L\'IFU doit contenir exactement 13 chiffres',
            'type.required' => 'Le type de facture est requis',
            'type.in' => 'Le type de facture doit être FV, FA, EV ou EA',
            'aib.in' => 'L\'AIB doit être A (1%) ou B (5%)',
            'reference.required_if' => 'La référence de la facture originale est obligatoire pour les factures d’avoir.',

            'client.name.required' => 'Le nom du client est requis',
            'client.ifu.regex' => 'L\'IFU du client doit contenir exactement 13 chiffres',

            'operator.name.string' => 'Le nom de l\'opérateur doit être une chaîne de caractères',
            'operator.name.max' => 'Le nom de l\'opérateur ne peut pas dépasser 255 caractères',

            'items.required' => 'Au moins un article est requis',
            'items.min' => 'Au moins un article est requis',
            'items.*.name.required' => 'Le nom de l\'article est requis',
            'items.*.price.required' => 'Le prix de l\'article est requis',
            'items.*.price.min' => 'Le prix doit être supérieur ou égal à 0',
            'items.*.quantity.required' => 'La quantité est requise',
            'items.*.quantity.gt' => 'La quantité doit être supérieure à 0',
            'items.*.taxGroup.required' => 'Le groupe de taxe est requis',
            'items.*.taxGroup.in' => 'Le groupe de taxe doit être A, B, C, D, E ou F',

            'payment.required' => 'Au moins un mode de paiement est requis',
            'payment.min' => 'Au moins un mode de paiement est requis',
            'payment.*.name.required' => 'Le type de paiement est requis',
            'payment.*.name.in' => 'Le type de paiement n’est pas supporté par e-MECeF',
            'payment.*.amount.required' => 'Le montant du paiement est requis',
            'payment.*.amount.gt' => 'Le montant doit être supérieur à 0',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et formater les données si nécessaire
        if ($this->has('items')) {
            $items = collect($this->input('items'))->map(function ($item) {
                return [
                    'name' => trim($item['name'] ?? ''),
                    'price' => (float) ($item['price'] ?? 0),
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'taxGroup' => strtoupper(trim($item['taxGroup'] ?? '')),
                    'code' => trim($item['code'] ?? ''),
                    'taxSpecific' => filled($item['taxSpecific'] ?? null) ? (float) $item['taxSpecific'] : null,
                    'originalPrice' => filled($item['originalPrice'] ?? null) ? (float) $item['originalPrice'] : null,
                    'priceModification' => filled($item['priceModification'] ?? null) ? trim($item['priceModification']) : null,
                ];
            })->toArray();

            $this->merge(['items' => $items]);
        }

        if ($this->has('payment')) {
            $payment = collect($this->input('payment'))->map(function ($pay) {
                return [
                    'name' => trim($pay['name'] ?? ''),
                    'amount' => (float) ($pay['amount'] ?? 0),
                ];
            })->toArray();

            $this->merge(['payment' => $payment]);
        }
    }
}
