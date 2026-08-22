<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\InvoiceRepositoryInterface;
use Banelsems\LaraSgmefQr\Contracts\ReferenceDataProviderInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Http\Filters\InvoiceFilters;
use Banelsems\LaraSgmefQr\Http\Requests\InvoiceRequest;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\TaxCalculatorService;
use Banelsems\LaraSgmefQr\Services\VerificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

/**
 * Contrôleur pour la gestion des factures
 */
class InvoiceController extends BaseController
{
    public function __construct(
        private readonly InvoiceManagerInterface $invoiceManager,
        private readonly TaxCalculatorService $taxCalculator,
        private readonly InvoiceRepositoryInterface $repository,
        private readonly InvoiceFilters $filters,
        private readonly ReferenceDataProviderInterface $referenceDataProvider,
        private readonly VerificationService $verificationService
    ) {
    }

    /**
     * Liste des factures
     */
    public function index(Request $request): View
    {
        $filterArray = $this->filters->fromRequest($request);
        $invoices = $this->repository->paginate($filterArray);

        return view('lara-sgmef-qr::invoices.index', compact('invoices'));
    }

    /**
     * Formulaire de création de facture
     */
    public function create(): View
    {
        $taxGroups = $this->referenceDataProvider->getTaxGroups();
        $paymentTypes = $this->referenceDataProvider->getPaymentTypes();
        $invoiceTypes = $this->referenceDataProvider->getInvoiceTypes();

        return view('lara-sgmef-qr::invoices.create', compact(
            'taxGroups',
            'paymentTypes',
            'invoiceTypes'
        ));
    }

    /**
     * Création d'une nouvelle facture
     */
    public function store(InvoiceRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // S'assurer qu'un opérateur est toujours défini
            if (empty($validatedData['operator']['name']) || empty($validatedData['operator']['id'])) {
                $validatedData['operator'] = [
                    'name' => config('lara_sgmef_qr.default_operator.name', 'Opérateur Principal'),
                    'id' => config('lara_sgmef_qr.default_operator.id', '1'),
                ];
            }

            $invoiceData = InvoiceRequestDto::fromArray($validatedData);
            $invoice = $this->invoiceManager->createInvoice($invoiceData);

            return redirect()
                ->route('sgmef.invoices.show', $invoice->uid)
                ->with('success', 'Facture créée avec succès !');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['invoice' => $e->getMessage()]);
        }
    }

    /**
     * Affichage d'une facture
     */
    public function show(string $uid): View
    {
        $invoice = $this->invoiceManager->getInvoice($uid);

        return view('lara-sgmef-qr::invoices.show', compact('invoice'));
    }

    /**
     * Confirmation d'une facture
     */
    public function confirm(string $uid): RedirectResponse
    {
        try {
            $invoice = $this->invoiceManager->confirmInvoice($uid);

            return redirect()
                ->route('sgmef.invoices.show', $uid)
                ->with('success', 'Facture confirmée avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['confirm' => $e->getMessage()]);
        }
    }

    /**
     * Annulation d'une facture
     */
    public function cancel(string $uid): RedirectResponse
    {
        try {
            $invoice = $this->invoiceManager->cancelInvoice($uid);

            return redirect()
                ->route('sgmef.invoices.show', $uid)
                ->with('success', 'Facture annulée avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }
    }

    /**
     * Synchronisation avec l'API
     */
    public function sync(string $uid): RedirectResponse
    {
        try {
            $invoice = $this->invoiceManager->syncInvoice($uid);

            return redirect()
                ->route('sgmef.invoices.show', $uid)
                ->with('success', 'Facture synchronisée avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }

    /**
     * Vérification post-confirmation d'une facture.
     */
    public function verify(string $uid): JsonResponse
    {
        try {
            $invoice = $this->invoiceManager->getInvoice($uid);
            $result = $this->verificationService->verify($invoice);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Prévisualisation d'une facture
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $requestData = $request->all();

            // S'assurer qu'un opérateur est toujours défini
            if (empty($requestData['operator']['name']) || empty($requestData['operator']['id'])) {
                $requestData['operator'] = [
                    'name' => config('lara_sgmef_qr.default_operator.name', 'Opérateur Principal'),
                    'id' => config('lara_sgmef_qr.default_operator.id', '1'),
                ];
            }

            $invoiceData = InvoiceRequestDto::fromArray($requestData);

            $summary = $this->taxCalculator->calculateInvoice($invoiceData->items);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_amount' => $summary['base'],
                    'total_with_tax' => $summary['total'],
                    'total_tax_amount' => $summary['tax'],
                    'total_aib_amount' => $summary['aib'],
                    'by_group' => $summary['by_group'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()->toArray(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Téléchargement PDF
     */
    public function downloadPdf(string $uid, Request $request): Response
    {
        $invoice = $this->invoiceManager->getInvoice($uid);

        if (!$invoice->isConfirmed()) {
            abort(400, 'Seules les factures confirmées peuvent être téléchargées en PDF');
        }

        $template = $request->input('template', config('lara_sgmef_qr.templates.default'));
        $format = $request->input('format', 'a4');

        // Génération du PDF (sera implémenté dans le système de templates)
        $pdf = $this->generatePdf($invoice, $template, $format);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"facture-{$invoice->uid}.pdf\"");
    }

    /**
     * Page d'impression
     */
    public function print(string $uid, ?string $template = null): View
    {
        $invoice = $this->invoiceManager->getInvoice($uid);
        $template ??= config('lara_sgmef_qr.templates.default');

        return view("lara-sgmef-qr::templates.{$template}", compact('invoice'));
    }

    /**
     * Génération PDF (placeholder)
     */
    private function generatePdf(Invoice $invoice, string $template, string $format): string
    {
        $view = view("lara-sgmef-qr::templates.{$template}", compact('invoice'));

        return Pdf::loadHTML($view->render())
            ->setPaper($format)
            ->output();
    }
}
