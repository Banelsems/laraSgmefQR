<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Orchestra\Testbench\TestCase;

/**
 * Vérifie que les trois commandes Artisan du package sont enregistrées
 * après la unification des namespaces de commands (Lot 2).
 */
class CommandRegistrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_emecef_generate_declaration_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('emecef:generate-declaration');
    }

    public function test_sgmef_check_config_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('sgmef:check-config');
    }

    public function test_emecef_export_stat_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('emecef:export-stat');
    }
}
