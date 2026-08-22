<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature\Jobs;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;

class CleanupCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../../src/Database/migrations');
    }

    public function test_cleanup_command_marks_expired_invoices(): void
    {
        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $exitCode = Artisan::call('sgmef:cleanup-expired');

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            InvoiceStatusEnum::EXPIRED,
            Invoice::first()->status
        );
    }

    public function test_cleanup_command_dry_run_does_not_modify(): void
    {
        Invoice::create([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'total_amount' => 1000,
            'expires_at' => now()->subMinute(),
        ]);

        $exitCode = Artisan::call('sgmef:cleanup-expired', ['--dry-run' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            InvoiceStatusEnum::PENDING,
            Invoice::first()->status
        );
    }

    public function test_cleanup_command_is_registered(): void
    {
        $this->assertTrue(
            array_key_exists('sgmef:cleanup-expired', Artisan::all())
        );
    }
}
