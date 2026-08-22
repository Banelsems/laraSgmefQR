<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_dashboard_index_returns_view_with_stats(): void
    {
        Invoice::factory()->create(['status' => InvoiceStatusEnum::CONFIRMED, 'total_amount' => 5000]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::PENDING]);

        $response = $this->get(route('sgmef.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('lara-sgmef-qr::dashboard.index');
        $response->assertViewHas('stats');
        $response->assertViewHas('recentInvoices');
        $response->assertViewHas('statusStats');
        $response->assertViewHas('dailyStats');
        $response->assertViewHas('topClients');
    }

    public function test_dashboard_stats_returns_json(): void
    {
        Invoice::factory()->create(['status' => InvoiceStatusEnum::CONFIRMED]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::PENDING]);

        $response = $this->getJson(route('sgmef.dashboard.stats'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_invoices',
            'pending_invoices',
            'confirmed_invoices',
            'error_invoices',
            'expired_pending',
            'total_amount',
            'generated_at',
        ]);
    }

    public function test_dashboard_shows_expired_alert_when_present(): void
    {
        Invoice::factory()->create([
            'status' => InvoiceStatusEnum::PENDING,
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->get(route('sgmef.dashboard'));

        $response->assertSee('expirée', false);
    }

    public function test_dashboard_shows_error_rate_alert_when_high(): void
    {
        Invoice::factory()->create(['status' => InvoiceStatusEnum::ERROR]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::ERROR]);
        Invoice::factory()->create(['status' => InvoiceStatusEnum::CONFIRMED]);

        $response = $this->get(route('sgmef.dashboard'));

        // Error rate > 10% should trigger alert
        $response->assertSee('erreur API', false);
    }

    public function test_dashboard_kpis_are_clickable_links(): void
    {
        $response = $this->get(route('sgmef.dashboard'));

        $response->assertSee(route('sgmef.invoices.index'), false);
        $response->assertSee(route('sgmef.invoices.index', ['status' => 'pending']), false);
        $response->assertSee(route('sgmef.invoices.index', ['status' => 'confirmed']), false);
    }
}
