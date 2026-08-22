<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

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
            'today_invoices' => Invoice::whereDate('created_at', today())->count(),
            'week_invoices' => Invoice::where('created_at', '>=', now()->startOfWeek())->count(),
            'month_invoices' => Invoice::where('created_at', '>=', now()->startOfMonth())->count(),
            'expired_pending' => Invoice::pending()->whereNotNull('expires_at')->where('expires_at', '<=', now())->count(),
            'api_error_rate' => $this->apiErrorRate(),
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

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::pending()->count(),
            'confirmed_invoices' => Invoice::confirmed()->count(),
            'error_invoices' => Invoice::withError()->count(),
            'expired_pending' => Invoice::pending()->whereNotNull('expires_at')->where('expires_at', '<=', now())->count(),
            'total_amount' => (float) Invoice::confirmed()->sum('total_amount'),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    private function apiErrorRate(): float
    {
        $total = Invoice::where('created_at', '>=', now()->subDays(30))->count();

        if ($total === 0) {
            return 0.0;
        }

        return round((Invoice::withError()->where('created_at', '>=', now()->subDays(30))->count() / $total) * 100, 2);
    }
}
