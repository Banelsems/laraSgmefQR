<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Exceptions\ValidationException;

/**
 * Gestionnaire de factures - Logique métier centralisée
 */
class InvoiceManager implements InvoiceManagerInterface
{
    public function __construct(
        private readonly SgmefApiClientInterface $apiClient
    ) {
    }

    /**
     * Crée une nouvelle facture
     */
    public function createInvoice(InvoiceRequestDto $invoiceData): Invoice
    {
        return DB::transaction(function () use ($invoiceData) {
            try {
                $this->validateInvoiceData($invoiceData);
                $apiResponse = $this->apiClient->createInvoice($invoiceData);

                $invoice = Invoice::create([
                    'uid' => $apiResponse->uid,
                    'ifu' => $invoiceData->ifu,
                    'customer_ifu' => $invoiceData->client->ifu,
                    'type' => $invoiceData->type,
                    'status' => InvoiceStatusEnum::PENDING,
                    'reference' => $invoiceData->reference,
                    'total_amount' => $apiResponse->totalAmount ?? $this->calculateRequestTotal($invoiceData),
                    'expires_at' => now()->addMinutes(2),
                    'raw_request' => $invoiceData->toArray(),
                    'raw_response' => $apiResponse->toArray(),
                ]);

                Log::info('Facture créée avec succès', [
                    'invoice_id' => $invoice->id,
                    'uid' => $invoice->uid,
                    'total_amount' => $invoice->total_amount,
                ]);

                return $invoice;
            } catch (ValidationException $e) {
                throw new InvoiceException('Données de facture invalides: ' . implode(', ', array_keys($e->errors())));
            } catch (SgmefApiException $e) {
                Log::error('Erreur API lors de la création de facture', [
                    'error' => $e->getMessage(),
                    'request_summary' => [
                        'ifu' => $invoiceData->ifu,
                        'customer_ifu_present' => filled($invoiceData->client->ifu),
                        'type' => $invoiceData->type,
                        'reference_present' => filled($invoiceData->reference),
                        'items_count' => count($invoiceData->items),
                        'payments_count' => count($invoiceData->payment),
                    ],
                ]);

                // Créer quand même l'enregistrement avec le statut erreur
                $invoice = Invoice::create([
                    'uid' => null,
                    'ifu' => $invoiceData->ifu,
                    'customer_ifu' => $invoiceData->client->ifu,
                    'type' => $invoiceData->type,
                    'status' => InvoiceStatusEnum::ERROR,
                    'reference' => $invoiceData->reference,
                    'total_amount' => $this->calculateRequestTotal($invoiceData),
                    'error_code' => (string) $e->getCode(),
                    'error_description' => $e->getMessage(),
                    'raw_request' => $invoiceData->toArray(),
                    'raw_response' => ['error' => $e->getMessage()],
                ]);

                throw new InvoiceException(
                    'Erreur lors de la création de la facture: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        });
    }

    /**
     * Confirme une facture et récupère les éléments de sécurité
     */
    public function confirmInvoice(string $uid): Invoice
    {
        $invoice = $this->getInvoiceByUid($uid);

        if ($invoice->status !== InvoiceStatusEnum::PENDING) {
            throw new InvoiceException(
                "La facture {$uid} ne peut pas être confirmée. Statut actuel: {$invoice->status->value}"
            );
        }

        return DB::transaction(function () use ($invoice) {
            try {
                // Appel à l'API pour confirmer la facture
                $securityElements = $this->apiClient->confirmInvoice($invoice->uid);

                if ($securityElements->hasError()) {
                    $invoice->update([
                        'status' => InvoiceStatusEnum::ERROR,
                        'error_code' => $securityElements->errorCode,
                        'error_description' => $securityElements->errorDesc,
                        'security_elements' => $securityElements->toArray(),
                    ]);

                    throw new InvoiceException(
                        "Erreur lors de la confirmation: {$securityElements->errorCode}: {$securityElements->errorDesc}"
                    );
                }

                // Mise à jour avec les éléments de sécurité
                $invoice->update([
                    'status' => InvoiceStatusEnum::CONFIRMED,
                    'qr_code_data' => $securityElements->qrCode,
                    'mecf_code' => $securityElements->codeMECeFDGI,
                    'security_elements' => $securityElements->toArray(),
                    'error_code' => null,
                    'error_description' => null,
                    'confirmed_at' => now(),
                    'raw_response' => array_merge(
                        $invoice->raw_response ?? [],
                        ['security_elements' => $securityElements->toArray()]
                    ),
                ]);

                Log::info('Facture confirmée avec succès', [
                    'invoice_id' => $invoice->id,
                    'uid' => $invoice->uid,
                    'mecf_code' => $invoice->mecf_code,
                ]);

                return $invoice;
            } catch (SgmefApiException $e) {
                Log::error('Erreur API lors de la confirmation de facture', [
                    'uid' => $invoice->uid,
                    'error' => $e->getMessage(),
                ]);

                $invoice->update([
                    'status' => InvoiceStatusEnum::ERROR,
                    'error_code' => (string) $e->getCode(),
                    'error_description' => $e->getMessage(),
                    'raw_response' => array_merge(
                        $invoice->raw_response ?? [],
                        ['confirmation_error' => $e->getMessage()]
                    ),
                ]);

                throw new InvoiceException(
                    'Erreur lors de la confirmation de la facture: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        });
    }

    /**
     * Annule une facture
     */
    public function cancelInvoice(string $uid): Invoice
    {
        $invoice = $this->getInvoiceByUid($uid);

        if (!in_array($invoice->status, [InvoiceStatusEnum::PENDING, InvoiceStatusEnum::CONFIRMED])) {
            throw new InvoiceException(
                "La facture {$uid} ne peut pas être annulée. Statut actuel: {$invoice->status->value}"
            );
        }

        return DB::transaction(function () use ($invoice) {
            try {
                // Appel à l'API pour annuler la facture
                $cancelResponse = $this->apiClient->cancelInvoice($invoice->uid);

                // Mise à jour du statut
                $invoice->update([
                    'status' => InvoiceStatusEnum::CANCELLED,
                    'cancelled_at' => now(),
                    'raw_response' => array_merge(
                        $invoice->raw_response ?? [],
                        ['cancellation' => $cancelResponse]
                    ),
                ]);

                Log::info('Facture annulée avec succès', [
                    'invoice_id' => $invoice->id,
                    'uid' => $invoice->uid,
                ]);

                return $invoice;
            } catch (SgmefApiException $e) {
                Log::error('Erreur API lors de l\'annulation de facture', [
                    'uid' => $invoice->uid,
                    'error' => $e->getMessage(),
                ]);

                throw new InvoiceException(
                    'Erreur lors de l\'annulation de la facture: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        });
    }

    /**
     * Récupère une facture par son UID
     */
    public function getInvoice(string $uid): Invoice
    {
        return $this->getInvoiceByUid($uid);
    }

    /**
     * Synchronise une facture avec l'API
     */
    public function syncInvoice(string $uid): Invoice
    {
        $invoice = $this->getInvoiceByUid($uid);

        if (!$invoice->uid) {
            throw new InvoiceException('Impossible de synchroniser une facture sans UID API');
        }

        try {
            $apiResponse = $this->apiClient->getInvoice($invoice->uid);

            $invoice->update([
                'raw_response' => array_merge(
                    $invoice->raw_response ?? [],
                    ['sync' => $apiResponse->toArray()]
                ),
            ]);

            Log::info('Facture synchronisée', [
                'invoice_id' => $invoice->id,
                'uid' => $invoice->uid,
            ]);

            return $invoice;
        } catch (SgmefApiException $e) {
            Log::warning('Erreur lors de la synchronisation', [
                'uid' => $invoice->uid,
                'error' => $e->getMessage(),
            ]);

            throw new InvoiceException(
                'Erreur lors de la synchronisation: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Récupère une facture par UID avec gestion d'erreur
     */
    private function getInvoiceByUid(string $uid): Invoice
    {
        $invoice = Invoice::where('uid', $uid)->first();

        if (!$invoice) {
            throw new InvoiceException("Facture introuvable avec l'UID: {$uid}");
        }

        return $invoice;
    }

    private function calculateRequestTotal(InvoiceRequestDto $invoiceData): float
    {
        return collect($invoiceData->items)->sum(function ($item): float {
            return (float) $item->price * (float) $item->quantity;
        });
    }

    private function validateInvoiceData(InvoiceRequestDto $invoiceData): void
    {
        if (!preg_match('/^\d{13}$/', $invoiceData->ifu)) {
            throw new InvoiceException('Données de facture invalides: ifu');
        }

        if (blank($invoiceData->client->name)) {
            throw new InvoiceException('Données de facture invalides: client.name');
        }

        if (in_array($invoiceData->type, ['FA', 'EA'], true) && blank($invoiceData->reference)) {
            throw new InvoiceException('Données de facture invalides: reference');
        }

        if (abs($this->calculateRequestTotal($invoiceData) - collect($invoiceData->payment)->sum('amount')) > 0.01) {
            throw new InvoiceException('Données de facture invalides: payment');
        }
    }
}
