<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour les informations de l'opérateur
 */
class OperatorDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name']
        );
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * Valide les données de l'opérateur
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Le nom de l\'opérateur est requis';
        }

        if (empty($this->id)) {
            $errors[] = 'L\'ID de l\'opérateur est requis';
        }

        return $errors;
    }
}
