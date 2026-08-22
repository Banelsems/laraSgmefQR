<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

/**
 * Tests fonctionnels pour CheckConfigCommand (sgmef:check-config).
 *
 * Vérifie que la commande valide les bonnes clés de configuration
 * (api_url, token, default_ifu) et non les clés erronées (api.url, api.token)
 * qui étaient utilisées avant le correctif v3.1.
 */
class CheckConfigCommandTest extends TestCase
{
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

    public function test_returns_success_when_all_required_config_is_present(): void
    {
        Config::set('lara_sgmef_qr.api_url', 'https://api.impots.bj/sygmef-emcf/api');
        Config::set('lara_sgmef_qr.token', 'valid-jwt-token');
        Config::set('lara_sgmef_qr.default_ifu', '1234567890123');

        $this->artisan('sgmef:check-config')
            ->expectsOutput('Vérification de la configuration de LaraSgmefQR...')
            ->expectsOutput('Configuration validée avec succès !')
            ->assertSuccessful();
    }

    public function test_returns_failure_when_api_url_is_missing(): void
    {
        Config::set('lara_sgmef_qr.api_url', null);
        Config::set('lara_sgmef_qr.token', 'valid-jwt-token');
        Config::set('lara_sgmef_qr.default_ifu', '1234567890123');

        $this->artisan('sgmef:check-config')
            ->expectsOutput('URL de l\'API e-MECeF manquant ou vide (clé : lara_sgmef_qr.api_url).')
            ->assertFailed();
    }

    public function test_returns_failure_when_token_is_missing(): void
    {
        Config::set('lara_sgmef_qr.api_url', 'https://api.impots.bj/sygmef-emcf/api');
        Config::set('lara_sgmef_qr.token', null);
        Config::set('lara_sgmef_qr.default_ifu', '1234567890123');

        $this->artisan('sgmef:check-config')
            ->expectsOutput('Token JWT e-MECeF manquant ou vide (clé : lara_sgmef_qr.token).')
            ->assertFailed();
    }

    public function test_returns_failure_when_default_ifu_is_missing(): void
    {
        Config::set('lara_sgmef_qr.api_url', 'https://api.impots.bj/sygmef-emcf/api');
        Config::set('lara_sgmef_qr.token', 'valid-jwt-token');
        Config::set('lara_sgmef_qr.default_ifu', null);

        $this->artisan('sgmef:check-config')
            ->expectsOutput('IFU par défaut manquant ou vide (clé : lara_sgmef_qr.default_ifu).')
            ->assertFailed();
    }

    public function test_returns_failure_when_all_config_is_missing(): void
    {
        Config::set('lara_sgmef_qr.api_url', null);
        Config::set('lara_sgmef_qr.token', null);
        Config::set('lara_sgmef_qr.default_ifu', null);

        $this->artisan('sgmef:check-config')
            ->assertFailed();
    }

    public function test_treats_empty_string_as_missing(): void
    {
        Config::set('lara_sgmef_qr.api_url', '   ');
        Config::set('lara_sgmef_qr.token', 'valid-jwt-token');
        Config::set('lara_sgmef_qr.default_ifu', '1234567890123');

        $this->artisan('sgmef:check-config')
            ->expectsOutput('URL de l\'API e-MECeF manquant ou vide (clé : lara_sgmef_qr.api_url).')
            ->assertFailed();
    }
}
