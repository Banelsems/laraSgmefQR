<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Repositories;

use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implémentation Eloquent du repository des factures.
 */
class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $attributes): Invoice
    {
        return Invoice::create($attributes);
    }

    public function findByUid(string $uid): Invoice
    {
        $invoice = Invoice::where('uid', $uid)->first();

        if ($invoice === null) {
            throw new InvoiceException("Facture introuvable pour l'UID : {$uid}");
        }

        return $invoice;
    }

    public function update(Invoice $invoice, array $attributes): Invoice
    {
        $invoice->update($attributes);
        $invoice->refresh();

        return $invoice;
    }

    public function updateStatus(Invoice $invoice, InvoiceStatusEnum $status): Invoice
    {
        return $this->update($invoice, ['status' => $status]);
    }

    public function paginate(array $filters = [], int $perPage = 15): mixed
    {
        $query = Invoice::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['ifu'])) {
            $query->where('ifu', $filters['ifu']);
        }

        if (isset($filters['customer_ifu'])) {
            $query->where('customer_ifu', $filters['customer_ifu']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['amount_min'])) {
            $query->where('total_amount', '>=', (int) $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('total_amount', '<=', (int) $filters['amount_max']);
        }

        if (isset($filters['search'])) {
            $this->applySearch($query, (string) $filters['search']);
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        // Whitelist stricte des colonnes triables
        $allowedSorts = ['created_at', 'total_amount', 'confirmed_at', 'type', 'status'];
        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Applique une recherche multi-champs avec bindings sécurisés.
     *
     * Les colonnes recherchées sont statiques (pas d'entrée utilisateur
     * dans les noms de colonnes). Le terme de recherche est passé via
     * binding paramétré, jamais interpolé dans le SQL.
     *
     * La recherche JSON sur raw_request->client->name utilise la syntaxe
     * path de Laravel qui génère des bindings compatibles SQLite/MySQL.
     */
    private function applySearch(mixed $query, string $search): void
    {
        $term = '%' . $search . '%';

        $query->where(function ($q) use ($term) {
            $q->where('uid', 'like', $term)
              ->orWhere('customer_ifu', 'like', $term)
              ->orWhere('mecf_code', 'like', $term)
              ->orWhere('reference', 'like', $term)
              ->orWhere('raw_request->client->name', 'like', $term);
        });
    }

    public function countByStatus(InvoiceStatusEnum $status): int
    {
        return Invoice::where('status', $status)->count();
    }

    public function findExpiredPending(): Collection
    {
        return Invoice::where('status', InvoiceStatusEnum::PENDING)
            ->where('expires_at', '<', now())
            ->get();
    }
}
