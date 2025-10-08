<?php

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Contrôleur API pour les endpoints AJAX
 */
class ApiController extends BaseController
{
    public function __construct(
        private readonly SgmefApiClientInterface $apiClient
    ) {}

    /**
     * Récupère les groupes de taxes
     */
    public function getTaxGroups(): JsonResponse
    {
        try {
            $cacheKey = config('lara_sgmef_qr.cache.prefix') . 'tax_groups';
            $ttl = config('lara_sgmef_qr.cache.ttl', 3600);

            $taxGroups = Cache::remember($cacheKey, $ttl, function () {
                return $this->apiClient->getTaxGroups();
            });

            return response()->json([
                'success' => true,
                'data' => $taxGroups
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des groupes de taxes : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les types de paiement
     */
    public function getPaymentTypes(): JsonResponse
    {
        try {
            $cacheKey = config('lara_sgmef_qr.cache.prefix') . 'payment_types';
            $ttl = config('lara_sgmef_qr.cache.ttl', 3600);

            $paymentTypes = Cache::remember($cacheKey, $ttl, function () {
                return $this->apiClient->getPaymentTypes();
            });

            return response()->json([
                'success' => true,
                'data' => $paymentTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de paiement : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les types de factures
     */
    public function getInvoiceTypes(): JsonResponse
    {
        try {
            $cacheKey = config('lara_sgmef_qr.cache.prefix') . 'invoice_types';
            $ttl = config('lara_sgmef_qr.cache.ttl', 3600);

            $invoiceTypes = Cache::remember($cacheKey, $ttl, function () {
                return $this->apiClient->getInvoiceTypes();
            });

            return response()->json([
                'success' => true,
                'data' => $invoiceTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de factures : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide un IFU
     */
    public function validateIfu(Request $request): JsonResponse
    {
        $request->validate([
            'ifu' => ['required', 'string', 'regex:/^\d{13}$/']
        ]);

        $ifu = $request->input('ifu');

        // Validation basique du format
        if (!preg_match('/^\d{13}$/', $ifu)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'IFU doit contenir exactement 13 chiffres'
            ], 422);
        }

        // TODO: Ajouter une validation plus poussée si l'API le permet
        // Par exemple, vérifier si l'IFU existe dans la base de données de l'administration

        return response()->json([
            'success' => true,
            'message' => 'IFU valide',
            'data' => [
                'ifu' => $ifu,
                'formatted' => $this->formatIfu($ifu)
            ]
        ]);
    }

    /**
     * Formate un IFU pour l'affichage
     */
    private function formatIfu(string $ifu): string
    {
        // Format: XXXX XXX XXX XXX
        return substr($ifu, 0, 4) . ' ' . 
               substr($ifu, 4, 3) . ' ' . 
               substr($ifu, 7, 3) . ' ' . 
               substr($ifu, 10, 3);
    }
}
