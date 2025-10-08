<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO principal pour la requête de création de facture
 */
class InvoiceRequestDto
{
    /**
     * @param InvoiceItemDto[] $items
     * @param PaymentDto[] $payment
     */
    public function __construct(
        public readonly string $ifu,
        public readonly string $type,
        public readonly array $items,
        public readonly ClientDto $client,
        public readonly OperatorDto $operator,
        public readonly array $payment,
        public readonly ?string $aib = null,
        public readonly ?string $reference = null
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn($item) => InvoiceItemDto::fromArray($item),
            $data['items'] ?? []
        );

        $payment = array_map(
            fn($payment) => PaymentDto::fromArray($payment),
            $data['payment'] ?? []
        );

        return new self(
            ifu: $data['ifu'],
            type: $data['type'],
            items: $items,
            client: ClientDto::fromArray($data['client'] ?? []),
            operator: OperatorDto::fromArray($data['operator'] ?? []),
            payment: $payment,
            aib: $data['aib'] ?? null,
            reference: $data['reference'] ?? null
        );
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function toArray(): array
    {
        return array_filter([
            'ifu' => $this->ifu,
            'type' => $this->type,
            'aib' => $this->aib,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'client' => $this->client->toArray(),
            'operator' => $this->operator->toArray(),
            'payment' => array_map(fn($payment) => $payment->toArray(), $this->payment),
            'reference' => $this->reference,
        ], fn($value) => $value !== null);
    }

    /**
     * Calcule le montant total de la facture
     */
    public function getTotalAmount(): float
    {
        return array_sum(array_map(
            fn($item) => $item->getTotalAmount(),
            $this->items
        ));
    }

    /**
     * Calcule le montant total des paiements
     */
    public function getTotalPaymentAmount(): float
    {
        return array_sum(array_map(
            fn($payment) => $payment->amount,
            $this->payment
        ));
    }

    /**
     * Valide toutes les données de la facture
     */
    public function validate(): array
    {
        $errors = [];

        // Validation des champs principaux
        if (empty($this->ifu)) {
            $errors[] = 'L\'IFU est requis';
        } elseif (!preg_match('/^\d{13}$/', $this->ifu)) {
            $errors[] = 'L\'IFU doit contenir exactement 13 chiffres';
        }

        if (empty($this->type)) {
            $errors[] = 'Le type de facture est requis';
        } elseif (!in_array($this->type, ['FV', 'FA', 'EV', 'EA'])) {
            $errors[] = 'Le type de facture doit être FV, FA, EV ou EA';
        }

        if ($this->aib && !in_array($this->aib, ['A', 'B'])) {
            $errors[] = 'L\'AIB doit être A (1%) ou B (5%)';
        }

        // Validation des articles
        if (empty($this->items)) {
            $errors[] = 'Au moins un article est requis';
        } else {
            foreach ($this->items as $index => $item) {
                $itemErrors = $item->validate();
                foreach ($itemErrors as $error) {
                    $errors[] = "Article " . ($index + 1) . ": " . $error;
                }
            }
        }

        // Validation du client
        $clientErrors = $this->client->validate();
        foreach ($clientErrors as $error) {
            $errors[] = "Client: " . $error;
        }

        // Validation de l'opérateur
        $operatorErrors = $this->operator->validate();
        foreach ($operatorErrors as $error) {
            $errors[] = "Opérateur: " . $error;
        }

        // Validation des paiements
        if (empty($this->payment)) {
            $errors[] = 'Au moins un mode de paiement est requis';
        } else {
            foreach ($this->payment as $index => $payment) {
                $paymentErrors = $payment->validate();
                foreach ($paymentErrors as $error) {
                    $errors[] = "Paiement " . ($index + 1) . ": " . $error;
                }
            }
        }

        // Validation de cohérence montants
        $totalAmount = $this->getTotalAmount();
        $totalPayment = $this->getTotalPaymentAmount();
        
        if (abs($totalAmount - $totalPayment) > 0.01) {
            $errors[] = "Le montant total des paiements ({$totalPayment}) ne correspond pas au montant total de la facture ({$totalAmount})";
        }

        return $errors;
    }
}
