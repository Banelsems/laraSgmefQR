<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour la réponse de création/consultation de facture de l'API.
 * Mappe la réponse JSON concise de l'API SyGM-eMCF vers un objet structuré.
 */
class InvoiceResponseDto
{
    /**
     * @param string $uid UID de la facture retourné par l'API.
     * @param float $totalAmount Montant total de la facture (mappé depuis 'total').
     * @param float|null $totalTaxAmount Montant total des taxes (mappé depuis 'ts').
     * @param float|null $totalAibAmount Montant de l'AIB (mappé depuis 'aib').
     * @param array|null $items Articles de la facture.
     * @param string|null $status Statut de la facture (pending, confirmed, etc.).
     * @param string|null $dateTime Date et heure de l'opération.
     */
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
     * Crée une instance depuis la réponse tableau de l'API SyGM-eMCF.
     *
     * @param array $data Les données décodées de la réponse JSON de l'API.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uid: $data['uid'],
            // API key: 'total' -> DTO property: 'totalAmount'
            totalAmount: (float) ($data['total'] ?? $data['totalAmount'] ?? 0.0),
            // API key: 'ts' (Total Taxes) -> DTO property: 'totalTaxAmount'
            totalTaxAmount: isset($data['ts']) || isset($data['totalTaxAmount'])
                ? (float) ($data['ts'] ?? $data['totalTaxAmount'] ?? 0.0)
                : null,
            // API key: 'aib' -> DTO property: 'totalAibAmount'
            totalAibAmount: isset($data['aib']) || isset($data['totalAibAmount'])
                ? (float) ($data['aib'] ?? $data['totalAibAmount'] ?? 0.0)
                : null,
            items: $data['items'] ?? null,
            status: $data['status'] ?? null,
            dateTime: $data['dateTime'] ?? $data['date'] ?? null
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
