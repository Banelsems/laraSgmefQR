<?php

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur pour le tableau de bord
 */
class DashboardController extends BaseController
{
    /**
     * Affiche le tableau de bord principal
     */
    public function index(): View
    {
        // Statistiques générales
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::pending()->count(),
            'confirmed_invoices' => Invoice::confirmed()->count(),
            'error_invoices' => Invoice::withError()->count(),
            'total_amount' => Invoice::confirmed()->sum('total_amount'),
        ];

        // Factures récentes
        $recentInvoices = Invoice::with([])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Statistiques par statut pour le graphique
        $statusStats = Invoice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status->value => $item->count];
            });

        // Évolution des factures sur les 30 derniers jours
        $dailyStats = Invoice::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(total_amount) as amount')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 5 des clients par montant
        $topClients = Invoice::select(
                'customer_ifu',
                DB::raw('count(*) as invoice_count'),
                DB::raw('sum(total_amount) as total_amount')
            )
            ->whereNotNull('customer_ifu')
            ->confirmed()
            ->groupBy('customer_ifu')
            ->orderBy('total_amount', 'desc')
            ->limit(5)
            ->get();

        return view('lara-sgmef-qr::dashboard.index', compact(
            'stats',
            'recentInvoices',
            'statusStats',
            'dailyStats',
            'topClients'
        ));
    }
}
