<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour la réponse de création de facture de l'API
 */
class InvoiceResponseDto
{
    public function __construct(
        public readonly string $uid,
        public readonly float $totalAmount,
        public readonly ?float $totalTaxAmount = null,
        public readonly ?float $totalAibAmount = null,
        public readonly ?array $items = null,
        public readonly ?string $status = null,
        public readonly ?string $dateTime = null
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uid: $data['uid'],
            totalAmount: (float) $data['totalAmount'],
            totalTaxAmount: isset($data['totalTaxAmount']) ? (float) $data['totalTaxAmount'] : null,
            totalAibAmount: isset($data['totalAibAmount']) ? (float) $data['totalAibAmount'] : null,
            items: $data['items'] ?? null,
            status: $data['status'] ?? null,
            dateTime: $data['dateTime'] ?? null
        );
    }

    /**
     * Convertit en tableau
     */
    public function toArray(): array
    {
        return array_filter([
            'uid' => $this->uid,
            'totalAmount' => $this->totalAmount,
            'totalTaxAmount' => $this->totalTaxAmount,
            'totalAibAmount' => $this->totalAibAmount,
            'items' => $this->items,
            'status' => $this->status,
            'dateTime' => $this->dateTime,
        ], fn($value) => $value !== null);
    }

    /**
     * Vérifie si la facture est en attente de confirmation
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === null;
    }

    /**
     * Vérifie si la facture a été confirmée
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Vérifie si la facture a été annulée
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
