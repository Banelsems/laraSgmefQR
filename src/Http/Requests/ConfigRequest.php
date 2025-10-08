<?php

namespace Banelsems\LaraSgmefQr\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request pour la validation de la configuration
 */
class ConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée par le middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'api_url' => ['required', 'url'],
            'token' => ['nullable', 'string', 'min:10'],
            'default_ifu' => ['nullable', 'string', 'regex:/^\d{13}$/'],
            'default_operator_name' => ['nullable', 'string', 'max:255'],
            'http_timeout' => ['nullable', 'integer', 'min:5', 'max:300'],
            'verify_ssl' => ['nullable', 'boolean'],
            'logging_enabled' => ['nullable', 'boolean'],
            'web_interface_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'api_url.required' => 'L\'URL de l\'API est requise',
            'api_url.url' => 'L\'URL de l\'API doit être une URL valide',
            'token.min' => 'Le token doit contenir au moins 10 caractères',
            'default_ifu.regex' => 'L\'IFU doit contenir exactement 13 chiffres',
            'http_timeout.min' => 'Le timeout doit être d\'au moins 5 secondes',
            'http_timeout.max' => 'Le timeout ne peut pas dépasser 300 secondes',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'api_url' => 'URL de l\'API',
            'token' => 'Token JWT',
            'default_ifu' => 'IFU par défaut',
            'default_operator_name' => 'Nom de l\'opérateur',
            'http_timeout' => 'Timeout HTTP',
            'verify_ssl' => 'Vérification SSL',
            'logging_enabled' => 'Logs activés',
            'web_interface_enabled' => 'Interface web activée',
        ];
    }
}
