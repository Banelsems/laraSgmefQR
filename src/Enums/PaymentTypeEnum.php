<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Enums;

/**
 * Types de paiement acceptés par l'API e-MECeF (DGI Bénin).
 *
 * La liste est extraite du code existant (PaymentDto #[In] validation)
 * et correspond aux valeurs documentées par l'API.
 */
enum PaymentTypeEnum: string
{
    case ESPECES = 'ESPECES';
    case VIREMENT = 'VIREMENT';
    case CARTE_BANCAIRE = 'CARTEBANCAIRE';
    case MOBILE_MONEY = 'MOBILEMONEY';
    case CHEQUES = 'CHEQUES';
    case CREDIT = 'CREDIT';
    case AUTRE = 'AUTRE';

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
     * Retourne le libellé humain pour l'affichage.
     */
    public function label(): string
    {
        return match ($this) {
            self::ESPECES => 'Espèces',
            self::VIREMENT => 'Virement',
            self::CARTE_BANCAIRE => 'Carte bancaire',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CHEQUES => 'Chèques',
            self::CREDIT => 'Crédit',
            self::AUTRE => 'Autre',
        };
    }
}
