<?php

namespace Banelsems\LaraSgmefQr\Http\Controllers;

use Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface;
use Banelsems\LaraSgmefQr\Http\Requests\ConfigRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Contrôleur pour la configuration du package
 */
class ConfigController extends BaseController
{
    public function __construct(
        private readonly SgmefApiClientInterface $apiClient
    ) {}

    /**
     * Affiche la page de configuration
     */
    public function index(): View
    {
        $config = [
            'api_url' => config('lara_sgmef_qr.api_url'),
            'token' => config('lara_sgmef_qr.token') ? '***' : null,
            'default_ifu' => config('lara_sgmef_qr.default_ifu'),
            'default_operator_name' => config('lara_sgmef_qr.default_operator_name'),
            'http_timeout' => config('lara_sgmef_qr.http_options.timeout'),
            'verify_ssl' => config('lara_sgmef_qr.http_options.verify'),
            'logging_enabled' => config('lara_sgmef_qr.logging.enabled'),
            'web_interface_enabled' => config('lara_sgmef_qr.web_interface.enabled'),
        ];

        return view('lara-sgmef-qr::config.index', compact('config'));
    }

    /**
     * Sauvegarde la configuration
     */
    public function store(ConfigRequest $request): RedirectResponse
    {
        try {
            $configPath = config_path('lara_sgmef_qr.php');
            
            if (!file_exists($configPath)) {
                return back()->withErrors(['config' => 'Fichier de configuration non trouvé. Veuillez publier la configuration d\'abord.']);
            }

            // Lire le fichier de configuration actuel
            $currentConfig = include $configPath;
            
            // Mettre à jour avec les nouvelles valeurs
            $newConfig = $this->updateConfigArray($currentConfig, $request->validated());
            
            // Écrire le nouveau fichier de configuration
            $this->writeConfigFile($configPath, $newConfig);

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
     * Test la connexion à l'API
     */
    public function testConnection(Request $request): JsonResponse
    {
        try {
            // Configurer temporairement le client avec les nouvelles données
            if ($request->filled('api_url')) {
                $this->apiClient->setBaseUrl($request->input('api_url'));
            }
            
            if ($request->filled('token')) {
                $this->apiClient->setCredentials($request->input('token'));
            }

            // Tester la connexion
            $status = $this->apiClient->getStatus();

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour le tableau de configuration
     */
    private function updateConfigArray(array $config, array $newData): array
    {
        if (isset($newData['api_url'])) {
            $config['api_url'] = $newData['api_url'];
        }

        if (isset($newData['token']) && $newData['token'] !== '***') {
            $config['token'] = $newData['token'];
        }

        if (isset($newData['default_ifu'])) {
            $config['default_ifu'] = $newData['default_ifu'];
        }

        if (isset($newData['default_operator_name'])) {
            $config['default_operator_name'] = $newData['default_operator_name'];
        }

        if (isset($newData['http_timeout'])) {
            $config['http_options']['timeout'] = (int) $newData['http_timeout'];
        }

        if (isset($newData['verify_ssl'])) {
            $config['http_options']['verify'] = (bool) $newData['verify_ssl'];
        }

        if (isset($newData['logging_enabled'])) {
            $config['logging']['enabled'] = (bool) $newData['logging_enabled'];
        }

        if (isset($newData['web_interface_enabled'])) {
            $config['web_interface']['enabled'] = (bool) $newData['web_interface_enabled'];
        }

        return $config;
    }

    /**
     * Écrit le fichier de configuration
     */
    private function writeConfigFile(string $path, array $config): void
    {
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Impossible d\'écrire le fichier de configuration');
        }
    }
}
