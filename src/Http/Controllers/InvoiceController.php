<?php

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Spatie\LaravelData\Exceptions\ValidationException;
use Banelsems\LaraSgmefQr\Http\Requests\InvoiceRequest;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Contrôleur pour la gestion des factures
 */
class InvoiceController extends BaseController
{
    public function __construct(
        private readonly InvoiceManagerInterface $invoiceManager,
        private readonly SgmefApiClientInterface $apiClient
    ) {}

    /**
     * Liste des factures
     */
    public function index(Request $request): View
    {
        $query = Invoice::query()->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('uid', 'like', "%{$search}%")
                  ->orWhere('customer_ifu', 'like', "%{$search}%")
                  ->orWhere('mecf_code', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15);

        return view('lara-sgmef-qr::invoices.index', compact('invoices'));
    }

    /**
     * Formulaire de création de facture
     */
    public function create(): View
    {
        $taxGroups = $this->apiClient->getTaxGroups();
        $paymentTypes = $this->apiClient->getPaymentTypes();
        $invoiceTypes = $this->apiClient->getInvoiceTypes();

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

            // Appel à l'API pour obtenir les totaux calculés
            $response = $this->apiClient->createInvoice($invoiceData);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_amount' => $response->totalAmount,
                    'total_tax_amount' => $response->totalTaxAmount,
                    'total_aib_amount' => $response->totalAibAmount,
                    'items' => $response->items,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
        $template = $template ?? config('lara_sgmef_qr.templates.default');

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
