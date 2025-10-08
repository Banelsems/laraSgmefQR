<?php

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * DTO pour les éléments de sécurité retournés après confirmation
 */
class SecurityElementsDto
{
    public function __construct(
        public readonly string $dateTime,
        public readonly string $qrCode,
        public readonly string $codeMECeFDGI,
        public readonly string $counters,
        public readonly string $nim,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorDesc = null
    ) {}

    /**
     * Crée une instance depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dateTime: $data['dateTime'],
            qrCode: $data['qrCode'],
            codeMECeFDGI: $data['codeMECeFDGI'],
            counters: $data['counters'],
            nim: $data['nim'],
            errorCode: $data['errorCode'] ?? null,
            errorDesc: $data['errorDesc'] ?? null
        );
    }

    /**
     * Convertit en tableau
     */
    public function toArray(): array
    {
        return array_filter([
            'dateTime' => $this->dateTime,
            'qrCode' => $this->qrCode,
            'codeMECeFDGI' => $this->codeMECeFDGI,
            'counters' => $this->counters,
            'nim' => $this->nim,
            'errorCode' => $this->errorCode,
            'errorDesc' => $this->errorDesc,
        ], fn($value) => $value !== null);
    }

    /**
     * Vérifie s'il y a une erreur
     */
    public function hasError(): bool
    {
        return $this->errorCode !== null && $this->errorCode !== 'OK';
    }

    /**
     * Retourne le message d'erreur complet
     */
    public function getErrorMessage(): ?string
    {
        if (!$this->hasError()) {
            return null;
        }

        return $this->errorCode . ': ' . ($this->errorDesc ?? 'Erreur inconnue');
    }

    /**
     * Vérifie si la facture est valide (pas d'erreur)
     */
    public function isValid(): bool
    {
        return !$this->hasError();
    }

    /**
     * Retourne les données formatées pour l'affichage
     */
    public function getDisplayData(): array
    {
        return [
            'Date/Heure' => $this->dateTime,
            'Code QR' => $this->qrCode,
            'Code MECeF/DGI' => $this->codeMECeFDGI,
            'Compteurs' => $this->counters,
            'NIM' => $this->nim,
        ];
    }
}
