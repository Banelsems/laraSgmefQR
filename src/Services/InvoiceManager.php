<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Events\InvoiceCancelled;
use Banelsems\LaraSgmefQr\Events\InvoiceConfirmationFailed;
use Banelsems\LaraSgmefQr\Events\InvoiceConfirmed;
use Banelsems\LaraSgmefQr\Events\InvoiceCreationFailed;
use Banelsems\LaraSgmefQr\Events\InvoiceCreated;
use Banelsems\LaraSgmefQr\Events\InvoiceSynchronized;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Gestionnaire de factures — orchestrateur du cycle de vie.
 *
 * Délègue la persistance au InvoiceRepositoryInterface et dispatche
 * des événements métier permettant à l'application hôte de réagir.
 */
class InvoiceManager implements InvoiceManagerInterface
{
    public function __construct(
        private readonly SgmefApiClientInterface $apiClient,
        private readonly InvoiceRepositoryInterface $repository
    ) {
    }

    /**
     * Crée une nouvelle facture.
     */
    public function createInvoice(InvoiceRequestDto $invoiceData): Invoice
    {
        return DB::transaction(function () use ($invoiceData) {
            try {
                $apiResponse = $this->apiClient->createInvoice($invoiceData);

                $invoice = $this->repository->create([
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

                Event::dispatch(new InvoiceCreated($invoice));

                return $invoice;
            } catch (\Illuminate\Validation\ValidationException $e) {
                throw new InvoiceException(
                    'Données de facture invalides: ' . implode(', ', array_keys($e->validator->failed()))
                );
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

                $invoice = $this->repository->create([
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

                Event::dispatch(new InvoiceCreationFailed($invoiceData, $e));

                throw new InvoiceException(
                    'Erreur lors de la création de la facture: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        });
    }

    /**
     * Confirme une facture et récupère les éléments de sécurité.
     */
    public function confirmInvoice(string $uid): Invoice
    {
        $invoice = $this->repository->findByUid($uid);

        if ($invoice->status !== InvoiceStatusEnum::PENDING) {
            throw new InvoiceException(
                "La facture {$uid} ne peut pas être confirmée. Statut actuel: {$invoice->status->value}"
            );
        }

        return DB::transaction(function () use ($invoice) {
            try {
                $securityElements = $this->apiClient->confirmInvoice($invoice->requireUid());

                if ($securityElements->hasError()) {
                    $this->repository->update($invoice, [
                        'status' => InvoiceStatusEnum::ERROR,
                        'error_code' => $securityElements->errorCode,
                        'error_description' => $securityElements->errorDesc,
                        'security_elements' => $securityElements->toArray(),
                    ]);

                    throw new InvoiceException(
                        "Erreur lors de la confirmation: {$securityElements->errorCode}: {$securityElements->errorDesc}"
                    );
                }

                $this->repository->update($invoice, [
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

                Event::dispatch(new InvoiceConfirmed($invoice));

                return $invoice;
            } catch (SgmefApiException $e) {
                Log::error('Erreur API lors de la confirmation de facture', [
                    'uid' => $invoice->uid,
                    'error' => $e->getMessage(),
                ]);

                $this->repository->update($invoice, [
                    'status' => InvoiceStatusEnum::ERROR,
                    'error_code' => (string) $e->getCode(),
                    'error_description' => $e->getMessage(),
                    'raw_response' => array_merge(
                        $invoice->raw_response ?? [],
                        ['confirmation_error' => $e->getMessage()]
                    ),
                ]);

                Event::dispatch(new InvoiceConfirmationFailed(
                    $invoice,
                    new InvoiceException('Erreur lors de la confirmation: ' . $e->getMessage(), 0, $e)
                ));

                throw new InvoiceException(
                    'Erreur lors de la confirmation de la facture: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        });
    }

    /**
     * Annule une facture.
     */
    public function cancelInvoice(string $uid): Invoice
    {
        $invoice = $this->repository->findByUid($uid);

        if (!in_array($invoice->status, [InvoiceStatusEnum::PENDING, InvoiceStatusEnum::CONFIRMED])) {
            throw new InvoiceException(
                "La facture {$uid} ne peut pas être annulée. Statut actuel: {$invoice->status->value}"
            );
        }

        return DB::transaction(function () use ($invoice) {
            try {
                $cancelResponse = $this->apiClient->cancelInvoice($invoice->requireUid());

                $this->repository->update($invoice, [
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

                Event::dispatch(new InvoiceCancelled($invoice));

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
     * Récupère une facture par son UID.
     */
    public function getInvoice(string $uid): Invoice
    {
        return $this->repository->findByUid($uid);
    }

    /**
     * Synchronise une facture avec l'API.
     */
    public function syncInvoice(string $uid): Invoice
    {
        $invoice = $this->repository->findByUid($uid);
        $apiUid = $invoice->requireUid();

        try {
            $apiResponse = $this->apiClient->getInvoice($apiUid);

            $this->repository->update($invoice, [
                'raw_response' => array_merge(
                    $invoice->raw_response ?? [],
                    ['sync' => $apiResponse->toArray()]
                ),
            ]);

            Log::info('Facture synchronisée', [
                'invoice_id' => $invoice->id,
                'uid' => $invoice->uid,
            ]);

            Event::dispatch(new InvoiceSynchronized($invoice));

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

    private function calculateRequestTotal(InvoiceRequestDto $invoiceData): int
    {
        $total = Money::from(0);

        foreach ($invoiceData->items as $item) {
            $lineTotal = Money::from($item->price)->multiply((float) $item->quantity);
            $total = $total->add($lineTotal);
        }

        return $total->getAmount();
    }
}
