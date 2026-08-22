<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Jobs;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNetworkException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefTimeoutException;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job async de création de facture auprès de l'API e-MECeF.
 *
 * SÉCURITÉ CRITIQUE : POST /invoice est considéré NON idempotent tant que
 * le contraire n'est pas prouvé par la documentation officielle.
 *
 * tries = 1 : aucune retry automatique après une erreur ambiguë (timeout,
 * network) car e-MECeF a pu recevoir la requête et créer la facture.
 * Un retry aveugle pourrait créer une facture en double.
 *
 * Le payload est un tableau validé (pas le DTO Spatie Data complet) pour
 * garantir une sérialisation fiable. Le DTO est reconstruit au handle().
 *
 * L'IFU et l'operator sont capturés dans le payload au moment du dispatch
 * pour éviter toute fuite de contexte request dans le worker.
 */
class CreateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * tries = 1 : POST non-idempotent, pas de retry automatique.
     */
    public int $tries = 1;

    public int $timeout = 60;

    /**
     * @param  array<string, mixed>  $data  Payload validé (DTO->toArray()).
     */
    public function __construct(
        public readonly array $data
    ) {
        $this->onQueue(config('lara_sgmef_qr.queue.queue', 'sgmef'));
    }

    public function handle(InvoiceManagerInterface $manager): ?Invoice
    {
        try {
            $dto = InvoiceRequestDto::from($this->data);

            return $manager->createInvoice($dto);
        } catch (InvoiceException $e) {
            // Erreur métier (validation, rejet DGI) — ne pas retry.
            Log::warning('CreateInvoiceJob: erreur métier', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (SgmefNetworkException | SgmefTimeoutException $e) {
            // Erreur ambiguë : e-MECeF a pu recevoir la requête.
            // Ne pas retry (tries=1). L'application hôte doit reconcilier.
            Log::error('CreateInvoiceJob: erreur ambiguë — reconciliation requise', [
                'error' => $e->getMessage(),
                'data_ifu' => $this->data['ifu'] ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Appelé après épuisement des tentatives (ici, après la 1re).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CreateInvoiceJob: échec définitif', [
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
            'data_ifu' => $this->data['ifu'] ?? null,
        ]);
    }
}
