<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour les informations client d'une facture
 */
class ClientDto
{
    public function __construct(
        public readonly ?string $ifu = null,
        public readonly ?string $name = null,
        public readonly ?string $contact = null,
        public readonly ?string $address = null
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ifu: $data['ifu'] ?? null,
            name: $data['name'] ?? null,
            contact: $data['contact'] ?? null,
            address: $data['address'] ?? null
        );
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function toArray(): array
    {
        return array_filter([
            'ifu' => $this->ifu,
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
        ], fn($value) => $value !== null);
    }

    /**
     * Valide les données du client
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Le nom du client est requis';
        }

        if ($this->ifu && !preg_match('/^\d{13}$/', $this->ifu)) {
            $errors[] = 'L\'IFU doit contenir exactement 13 chiffres';
        }

        return $errors;
    }
}
