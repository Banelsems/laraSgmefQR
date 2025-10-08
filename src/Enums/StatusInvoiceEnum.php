<?php

namespace Banelsems\LaraSgmefQr\Enums;

/**
 * Enum pour les statuts de facture
 */
enum InvoiceStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case ERROR = 'error';

    /**
     * Retourne la couleur associée au statut pour l'affichage
     */
    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'green',
            self::CANCELLED => 'gray',
            self::ERROR => 'red',
        };
    }

    /**
     * Retourne le libellé français du statut
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmée',
            self::CANCELLED => 'Annulée',
            self::ERROR => 'Erreur',
        };
    }

    /**
     * Vérifie si le statut permet la confirmation
     */
    public function canBeConfirmed(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Vérifie si le statut permet l'annulation
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }
}