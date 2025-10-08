<?php

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
            'reference' => ['nullable', 'string', 'max:255'],
            
            // Client
            'client.name' => ['required', 'string', 'max:255'],
            'client.ifu' => ['nullable', 'string', 'regex:/^\d{13}$/'],
            'client.contact' => ['nullable', 'string', 'max:255'],
            'client.address' => ['nullable', 'string', 'max:500'],
            
            // Opérateur
            'operator.id' => ['required'],
            'operator.name' => ['required', 'string', 'max:255'],
            
            // Articles
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.taxGroup' => ['required', 'string', 'in:A,B,C,D,E,F'],
            'items.*.code' => ['nullable', 'string', 'max:100'],
            
            // Paiements
            'payment' => ['required', 'array', 'min:1'],
            'payment.*.name' => ['required', 'string', 'max:255'],
            'payment.*.amount' => ['required', 'numeric', 'min:0'],
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
            
            'client.name.required' => 'Le nom du client est requis',
            'client.ifu.regex' => 'L\'IFU du client doit contenir exactement 13 chiffres',
            
            'operator.id.required' => 'L\'ID de l\'opérateur est requis',
            'operator.name.required' => 'Le nom de l\'opérateur est requis',
            
            'items.required' => 'Au moins un article est requis',
            'items.min' => 'Au moins un article est requis',
            'items.*.name.required' => 'Le nom de l\'article est requis',
            'items.*.price.required' => 'Le prix de l\'article est requis',
            'items.*.price.min' => 'Le prix doit être supérieur ou égal à 0',
            'items.*.quantity.required' => 'La quantité est requise',
            'items.*.quantity.min' => 'La quantité doit être d\'au moins 1',
            'items.*.taxGroup.required' => 'Le groupe de taxe est requis',
            'items.*.taxGroup.in' => 'Le groupe de taxe doit être A, B, C, D, E ou F',
            
            'payment.required' => 'Au moins un mode de paiement est requis',
            'payment.min' => 'Au moins un mode de paiement est requis',
            'payment.*.name.required' => 'Le type de paiement est requis',
            'payment.*.amount.required' => 'Le montant du paiement est requis',
            'payment.*.amount.min' => 'Le montant doit être supérieur ou égal à 0',
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
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'taxGroup' => strtoupper(trim($item['taxGroup'] ?? '')),
                    'code' => trim($item['code'] ?? ''),
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
