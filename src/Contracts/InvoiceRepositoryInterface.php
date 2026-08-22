<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;

/**
 * Repository métier pour la persistance des factures.
 *
 * Encapsule les accès Eloquent qui ont une valeur métier,
 * permettant de substituer l'implémentation pour les tests
 * ou pour une autre source de données.
 */
interface InvoiceRepositoryInterface
{
    /**
     * Crée une facture en base.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Invoice;

    /**
     * Récupère une facture par son UID e-MECeF.
     *
     * @throws \Banelsems\LaraSgmefQr\Exceptions\InvoiceException si la facture n'existe pas.
     */
    public function findByUid(string $uid): Invoice;

    /**
     * Met à jour une facture.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Invoice $invoice, array $attributes): Invoice;

    /**
     * Met à jour le statut d'une facture.
     */
    public function updateStatus(Invoice $invoice, InvoiceStatusEnum $status): Invoice;

    /**
     * Récupère une liste paginée de factures avec filtres optionnels.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15): mixed;

    /**
     * Compte les factures par statut.
     */
    public function countByStatus(InvoiceStatusEnum $status): int;

    /**
     * Récupère les factures pending expirées (expires_at < now).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Invoice>
     */
    public function findExpiredPending(): mixed;
}
