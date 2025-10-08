<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour les informations de paiement
 */
class PaymentDto
{
    public function __construct(
        public readonly string $name,
        public readonly float $amount
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            amount: (float) $data['amount']
        );
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'amount' => $this->amount,
        ];
    }

    /**
     * Valide les données de paiement
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Le type de paiement est requis';
        }

        if ($this->amount <= 0) {
            $errors[] = 'Le montant du paiement doit être supérieur à 0';
        }

        return $errors;
    }
}
