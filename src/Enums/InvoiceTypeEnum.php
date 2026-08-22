<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Enums;

/**
 * Types de facture acceptés par l'API e-MECeF (DGI Bénin).
 *
 * - FV : Facture de Vente (standard)
 * - FA : Facture d'Avoir (annulation/remboursement d'une FV)
 * - EV : Envoi de Vente (équivalent électronique)
 * - EA : Envoi d'Avoir (annulation/remboursement d'une EV)
 *
 * FA et EA nécessitent une référence à la facture originale.
 */
enum InvoiceTypeEnum: string
{
    case FV = 'FV';
    case FA = 'FA';
    case EV = 'EV';
    case EA = 'EA';

    /**
     * Retourne la liste des valeurs valides pour la validation.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Vérifie si ce type est un avoir (nécessite une référence).
     */
    public function isCreditNote(): bool
    {
        return $this === self::FA || $this === self::EA;
    }

    /**
     * Retourne le type de facture originale correspondant à cet avoir.
     *
     * FA → FV, EA → EV. Pour les types non-avoir, retourne self.
     */
    public function originalType(): self
    {
        return match ($this) {
            self::FA => self::FV,
            self::EA => self::EV,
            default => $this,
        };
    }

    /**
     * Retourne le libellé humain pour l'affichage.
     */
    public function label(): string
    {
        return match ($this) {
            self::FV => 'Facture de Vente',
            self::FA => 'Facture d\'Avoir',
            self::EV => 'Envoi de Vente',
            self::EA => 'Envoi d\'Avoir',
        };
    }
}
