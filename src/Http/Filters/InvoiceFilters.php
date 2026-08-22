<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Http\Filters;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Illuminate\Http\Request;

/**
 * Filtres HTTP whitelistés pour la liste des factures.
 *
 * Empêche la transformation directe de paramètres HTTP en noms
 * de colonnes SQL non contrôlés. Normalise les montants en
 * integer minor units (cohérent avec le value object Money
 * introduit au Lot 3).
 */
class InvoiceFilters
{
    /**
     * Colonnes autorisées pour le tri.
     */
    private const ALLOWED_SORTS = ['created_at', 'total_amount', 'confirmed_at', 'type', 'status'];

    /**
     * Types de facture autorisés.
     */
    private const ALLOWED_TYPES = ['FV', 'FA', 'EV', 'EA'];

    /**
     * Longueur maximale d'une recherche (protection anti-abus).
     */
    private const SEARCH_MAX_LENGTH = 255;

    /**
     * Extrait les filtres validés depuis une Request HTTP.
     *
     * @return array<string, mixed>
     */
    public function fromRequest(Request $request): array
    {
        $filters = [];

        $status = $request->input('status');
        if ($status !== null && $this->isValidStatus((string) $status)) {
            $filters['status'] = $status;
        }

        $type = $request->input('type');
        if ($type !== null && in_array($type, self::ALLOWED_TYPES, true)) {
            $filters['type'] = $type;
        }

        $ifu = $request->input('ifu');
        if ($this->isNonEmptyString($ifu)) {
            $filters['ifu'] = $ifu;
        }

        $dateFrom = $request->input('date_from');
        if ($this->isNonEmptyString($dateFrom)) {
            $filters['date_from'] = $dateFrom;
        }

        $dateTo = $request->input('date_to');
        if ($this->isNonEmptyString($dateTo)) {
            $filters['date_to'] = $dateTo;
        }

        $amountMin = $this->parseAmount($request->input('amount_min'));
        $amountMax = $this->parseAmount($request->input('amount_max'));

        // Si min > max, la plage est invalide : on ignore les deux bornes
        // plutôt que d'appliquer un filtre contradictoire qui renverrait
        // une collection vide sans explication pour l'utilisateur.
        if ($amountMin !== null && $amountMax !== null && $amountMin > $amountMax) {
            // plage invalide — on ne conserve aucune borne
        } else {
            if ($amountMin !== null) {
                $filters['amount_min'] = $amountMin;
            }
            if ($amountMax !== null) {
                $filters['amount_max'] = $amountMax;
            }
        }

        $search = $request->input('search');
        if ($this->isNonEmptyString($search)) {
            $search = trim((string) $search);
            if ($search !== '' && mb_strlen($search) <= self::SEARCH_MAX_LENGTH) {
                $filters['search'] = $search;
            }
        }

        $sort = $request->input('sort', 'created_at');
        $filters['sort'] = in_array($sort, self::ALLOWED_SORTS, true) ? $sort : 'created_at';

        $direction = $request->input('direction', 'desc');
        $filters['direction'] = $direction === 'asc' ? 'asc' : 'desc';

        return $filters;
    }

    private function isValidStatus(string $status): bool
    {
        return InvoiceStatusEnum::tryFrom($status) !== null;
    }

    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    /**
     * Normalise un montant HTTP en integer minor units (XOF).
     *
     * Accepte les entiers et décimaux (ex. "1000" ou "1000.50").
     * Rejette : null, chaînes non numériques, valeurs négatives.
     *
     * @return int|null Montant en minor units, ou null si invalide/absent.
     */
    private function parseAmount(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        if (!is_numeric($string)) {
            return null;
        }

        $float = (float) $string;
        if ($float < 0) {
            return null;
        }

        // XOF = 0 décimales : arrondi vers le plus proche
        return (int) round($float);
    }
}
