<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\DTOs\ApiStatusDto;
use Banelsems\LaraSgmefQr\Services\SgmefConnectionTester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase;

/**
 * Tests fonctionnels pour ConfigController
 */
class ConfigControllerTest extends TestCase
{
    use RefreshDatabase;

    private $connectionTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionTester = Mockery::mock(SgmefConnectionTester::class);
        $this->app->instance(SgmefConnectionTester::class, $this->connectionTester);

        @unlink(config_path('lara_sgmef_qr.php'));
    }

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function tearDown(): void
    {
        @unlink(config_path('lara_sgmef_qr.php'));
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test d'affichage de la page de configuration
     */
    public function test_can_view_config_page(): void
    {
        Config::set('lara_sgmef_qr.api_url', 'https://test-api.example.com');
        Config::set('lara_sgmef_qr.token', 'test-token');
        Config::set('lara_sgmef_qr.default_ifu', '1234567890123');

        $response = $this->get(route('sgmef.config.index'));

        $response->assertStatus(200);
        $response->assertViewIs('lara-sgmef-qr::config.index');
        $response->assertViewHas('config');
    }

    /**
     * Test de sauvegarde de configuration avec données valides
     */
    public function test_can_save_config_with_valid_data(): void
    {
        $configData = [
            'api_url' => 'https://new-api.example.com',
            'token' => 'new-test-token',
            'default_ifu' => '9876543210987',
            'default_operator_name' => 'New Operator',
            'http_timeout' => 60,
            'verify_ssl' => true,
            'logging_enabled' => true,
            'web_interface_enabled' => true,
        ];

        // Mock du fichier de configuration existant
        $this->mockConfigFile();

        $response = $this->post(route('sgmef.config.store'), $configData);

        $response->assertRedirect(route('sgmef.config.index'));
        $response->assertSessionHas('success');
    }

    /**
     * Test de sauvegarde avec données invalides
     */
    public function test_save_config_fails_with_invalid_data(): void
    {
        $invalidData = [
            'api_url' => 'not-a-valid-url',
            'default_ifu' => '123', // IFU trop court
            'http_timeout' => 1, // Timeout trop court
        ];

        $response = $this->post(route('sgmef.config.store'), $invalidData);

        $response->assertSessionHasErrors(['api_url', 'default_ifu', 'http_timeout']);
    }

    /**
     * Test de test de connexion réussi.
     *
     * Depuis Lot 5, testConnection utilise SgmefConnectionTester qui crée
     * une instance isolée via la factory — ne mute pas le singleton partagé.
     */
    public function test_can_test_connection_successfully(): void
    {
        $this->connectionTester
            ->shouldReceive('test')
            ->with('https://test-api.example.com', 'test-token')
            ->andReturn(ApiStatusDto::from(['status' => 'OK', 'version' => '1.0']));

        $response = $this->getJson(route('sgmef.config.test', [
            'api_url' => 'https://test-api.example.com',
            'token' => 'test-token',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Connexion réussie !',
        ]);
    }

    /**
     * Test de test de connexion échoué.
     */
    public function test_connection_test_fails_with_invalid_credentials(): void
    {
        $this->connectionTester
            ->shouldReceive('test')
            ->with('https://test-api.example.com', 'invalid-token')
            ->andThrow(new \Exception('Unauthorized'));

        $response = $this->getJson(route('sgmef.config.test', [
            'api_url' => 'https://test-api.example.com',
            'token' => 'invalid-token',
        ]));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Erreur de connexion : Unauthorized',
        ]);
    }

    /**
     * Test que testConnection ne mute pas le singleton SgmefApiClient.
     *
     * Vérifie qu'aucun appel à setBaseUrl/setCredentials n'est fait
     * sur le client partagé.
     */
    public function test_connection_does_not_mutate_shared_client(): void
    {
        $apiClient = Mockery::mock(\Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class);
        $apiClient->shouldNotReceive('setBaseUrl');
        $apiClient->shouldNotReceive('setCredentials');
        $this->app->instance(\Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class, $apiClient);

        $this->connectionTester
            ->shouldReceive('test')
            ->andReturn(ApiStatusDto::from(['status' => 'OK', 'version' => '1.0']));

        $this->getJson(route('sgmef.config.test', [
            'api_url' => 'https://test-api.example.com',
            'token' => 'test-token',
        ]));

        // Si on arrive ici sans erreur, le singleton n'a pas été muté
        $this->assertTrue(true);
    }

    /**
     * Test de validation des champs requis
     */
    public function test_validates_required_fields(): void
    {
        $response = $this->post(route('sgmef.config.store'), []);

        $response->assertSessionHasErrors(['api_url']);
    }

    /**
     * Test de validation de l'URL API
     */
    public function test_validates_api_url_format(): void
    {
        $response = $this->post(route('sgmef.config.store'), [
            'api_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['api_url']);
    }

    /**
     * Test de validation de l'IFU
     */
    public function test_validates_ifu_format(): void
    {
        $response = $this->post(route('sgmef.config.store'), [
            'api_url' => 'https://valid-url.com',
            'default_ifu' => 'invalid-ifu',
        ]);

        $response->assertSessionHasErrors(['default_ifu']);
    }

    /**
     * Test de validation du timeout HTTP
     */
    public function test_validates_http_timeout_range(): void
    {
        $response = $this->post(route('sgmef.config.store'), [
            'api_url' => 'https://valid-url.com',
            'http_timeout' => 1, // Trop petit
        ]);

        $response->assertSessionHasErrors(['http_timeout']);

        $response = $this->post(route('sgmef.config.store'), [
            'api_url' => 'https://valid-url.com',
            'http_timeout' => 500, // Trop grand
        ]);

        $response->assertSessionHasErrors(['http_timeout']);
    }

    /**
     * Test de gestion d'erreur lors de l'écriture du fichier
     */
    public function test_handles_file_write_error(): void
    {
        // Simuler un fichier de configuration non trouvé
        $configData = [
            'api_url' => 'https://test-api.example.com',
        ];

        $response = $this->post(route('sgmef.config.store'), $configData);

        $response->assertSessionHasErrors(['config']);
    }

    /**
     * Mock du fichier de configuration pour les tests.
     *
     * Utilise la syntaxe [ ] (comme le fichier de config réel) pour
     * être compatible avec le FileConfigWriter qui fait des remplacements
     * ciblés par regex.
     */
    private function mockConfigFile(): void
    {
        $configPath = config_path('lara_sgmef_qr.php');

        if (!file_exists(dirname($configPath))) {
            mkdir(dirname($configPath), 0o755, true);
        }

        $content = <<<'PHP'
<?php

return [
    'api_url' => 'https://old-api.example.com',
    'token' => 'old-token',
    'default_ifu' => '1111111111111',
    'default_operator_name' => 'Old Operator',
    'http_options' => [
        'timeout' => 30,
        'verify' => true,
    ],
    'logging' => [
        'enabled' => true,
    ],
    'web_interface' => [
        'enabled' => true,
    ],
];
PHP;

        file_put_contents($configPath, $content);
    }
}
