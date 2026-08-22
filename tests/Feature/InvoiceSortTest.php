<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class InvoiceSortTest extends TestCase
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

    public function test_index_shows_sort_links_in_headers(): void
    {
        Invoice::factory()->create(['uid' => 'uid-1']);

        $response = $this->get(route('sgmef.invoices.index'));

        $response->assertStatus(200);
        // Sort links should be present for sortable columns
        $response->assertSee('sort=total_amount', false);
        $response->assertSee('sort=created_at', false);
        $response->assertSee('sort=status', false);
    }

    public function test_index_applies_sort_ascending(): void
    {
        Invoice::factory()->create(['uid' => 'uid-a', 'total_amount' => 100]);
        Invoice::factory()->create(['uid' => 'uid-b', 'total_amount' => 500]);

        $response = $this->get(route('sgmef.invoices.index', ['sort' => 'total_amount', 'direction' => 'asc']));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    public function test_index_applies_sort_descending(): void
    {
        Invoice::factory()->create(['uid' => 'uid-a', 'total_amount' => 100]);
        Invoice::factory()->create(['uid' => 'uid-b', 'total_amount' => 500]);

        $response = $this->get(route('sgmef.invoices.index', ['sort' => 'total_amount', 'direction' => 'desc']));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    public function test_index_preserves_filters_in_sort_links(): void
    {
        Invoice::factory()->create(['uid' => 'uid-1', 'status' => InvoiceStatusEnum::CONFIRMED]);

        $response = $this->get(route('sgmef.invoices.index', ['status' => 'confirmed', 'search' => 'uid-1']));

        $response->assertStatus(200);
        // Sort links should preserve existing filter params
        $response->assertSee('status=confirmed', false);
        $response->assertSee('search=uid-1', false);
    }

    public function test_index_shows_active_sort_indicator(): void
    {
        Invoice::factory()->create(['uid' => 'uid-1']);

        $response = $this->get(route('sgmef.invoices.index', ['sort' => 'total_amount', 'direction' => 'desc']));

        $response->assertStatus(200);
        // The active sort column should show arrow-down icon
        $response->assertSee('fa-arrow-down', false);
    }
}
