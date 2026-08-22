<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Http\Requests\ConfigRequest;
use Banelsems\LaraSgmefQr\Services\PackageConfigurationService;
use Banelsems\LaraSgmefQr\Services\SgmefConnectionTester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Contrôleur pour la configuration du package.
 *
 * Adaptateur HTTP fin : délègue à PackageConfigurationService
 * pour la persistance et à SgmefConnectionTester pour les tests
 * de connexion. Ne mute pas le singleton SgmefApiClient.
 */
class ConfigController extends BaseController
{
    public function __construct(
        private readonly PackageConfigurationService $configurationService,
        private readonly SgmefConnectionTester $connectionTester
    ) {
    }

    /**
     * Affiche la page de configuration.
     */
    public function index(): View
    {
        $config = $this->configurationService->current();

        return view('lara-sgmef-qr::config.index', compact('config'));
    }

    /**
     * Sauvegarde la configuration.
     */
    public function store(ConfigRequest $request): RedirectResponse
    {
        try {
            $configPath = config_path('lara_sgmef_qr.php');

            if (!file_exists($configPath)) {
                return back()->withErrors(['config' => 'Fichier de configuration non trouvé. Veuillez publier la configuration d\'abord.']);
            }

            $this->configurationService->update($request->validated(), $configPath);

            return redirect()
                ->route('sgmef.config.index')
                ->with('success', 'Configuration sauvegardée avec succès !');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['config' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()]);
        }
    }

    /**
     * Test la connexion à l'API.
     *
     * Construit une instance isolée de SgmefApiClient via la factory,
     * sans muter le singleton partagé.
     */
    public function testConnection(Request $request): JsonResponse
    {
        try {
            $apiUrl = (string) $request->input('api_url', config('lara_sgmef_qr.api_url'));
            $token = $request->filled('token') ? (string) $request->input('token') : null;

            $status = $this->connectionTester->test($apiUrl, $token);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage(),
            ], 400);
        }
    }
}
