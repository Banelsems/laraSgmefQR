<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour un article de facture
 */
class InvoiceItemDto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $quantity,
        public readonly string $taxGroup,
        public readonly ?string $code = null,
        public readonly ?float $originalPrice = null,
        public readonly ?string $priceModification = null
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            price: (float) $data['price'],
            quantity: (int) $data['quantity'],
            taxGroup: $data['taxGroup'],
            code: $data['code'] ?? null,
            originalPrice: isset($data['originalPrice']) ? (float) $data['originalPrice'] : null,
            priceModification: $data['priceModification'] ?? null
        );
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'taxGroup' => $this->taxGroup,
            'originalPrice' => $this->originalPrice,
            'priceModification' => $this->priceModification,
        ], fn($value) => $value !== null);
    }

    /**
     * Calcule le montant total de l'article
     */
    public function getTotalAmount(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Valide les données de l'article
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Le nom de l\'article est requis';
        }

        if ($this->price <= 0) {
            $errors[] = 'Le prix doit être supérieur à 0';
        }

        if ($this->quantity <= 0) {
            $errors[] = 'La quantité doit être supérieure à 0';
        }

        if (empty($this->taxGroup)) {
            $errors[] = 'Le groupe de taxe est requis';
        }

        if ($this->taxGroup && !in_array($this->taxGroup, ['A', 'B', 'C', 'D', 'E', 'F'])) {
            $errors[] = 'Le groupe de taxe doit être A, B, C, D, E ou F';
        }

        return $errors;
    }
}
