<?php

namespace Banelsems\LaraSgmefQr\Models;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle Invoice pour le tracking complet des factures
 * 
 * @property int $id
 * @property string|null $uid UID retourné par l'API SyGM-eMCF
 * @property string $ifu IFU de l'émetteur
 * @property string|null $customer_ifu IFU du client
 * @property string $type Type de facture (FV, FA, EV, EA)
 * @property InvoiceStatusEnum $status Statut de la facture
 * @property float $total_amount Montant total de la facture
 * @property array|null $raw_request Données brutes de la requête
 * @property array|null $raw_response Données brutes de la réponse API
 * @property string|null $qr_code_data Données du QR code
 * @property string|null $mecf_code Code MECeF/DGI
 * @property \Carbon\Carbon|null $confirmed_at Date de confirmation
 * @property \Carbon\Carbon|null $cancelled_at Date d'annulation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'ifu',
        'customer_ifu',
        'type',
        'status',
        'total_amount',
        'raw_request',
        'raw_response',
        'qr_code_data',
        'mecf_code',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => InvoiceStatusEnum::class,
        'total_amount' => 'decimal:2',
        'raw_request' => 'array',
        'raw_response' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, InvoiceStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les factures confirmées
     */
    public function scopeConfirmed($query)
    {
        return $query->byStatus(InvoiceStatusEnum::CONFIRMED);
    }

    /**
     * Scope pour les factures en attente
     */
    public function scopePending($query)
    {
        return $query->byStatus(InvoiceStatusEnum::PENDING);
    }

    /**
     * Scope pour les factures avec erreur
     */
    public function scopeWithError($query)
    {
        return $query->byStatus(InvoiceStatusEnum::ERROR);
    }

    /**
     * Vérifie si la facture peut être confirmée
     */
    public function canBeConfirmed(): bool
    {
        return $this->status->canBeConfirmed() && $this->uid !== null;
    }

    /**
     * Vérifie si la facture peut être annulée
     */
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled() && $this->uid !== null;
    }

    /**
     * Vérifie si la facture est confirmée
     */
    public function isConfirmed(): bool
    {
        return $this->status === InvoiceStatusEnum::CONFIRMED;
    }

    /**
     * Vérifie si la facture a une erreur
     */
    public function hasError(): bool
    {
        return $this->status === InvoiceStatusEnum::ERROR;
    }

    /**
     * Retourne le badge de statut formaté
     */
    public function getStatusBadge(): array
    {
        return [
            'label' => $this->status->getLabel(),
            'color' => $this->status->getColor(),
        ];
    }

    /**
     * Retourne les informations de debug pour les développeurs
     */
    public function getDebugInfo(): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'status' => $this->status->value,
            'has_qr_code' => !empty($this->qr_code_data),
            'has_mecf_code' => !empty($this->mecf_code),
            'created_at' => $this->created_at?->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
        ];
    }
}