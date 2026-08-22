<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\Services\DefaultIfuResolver;
use Orchestra\Testbench\TestCase;

class DefaultIfuResolverTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_resolve_default_ifu(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', []);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $this->assertSame('1234567890123', $resolver->resolve());
    }

    public function test_resolve_with_context_via_ifu_map(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', []);
        config()->set('lara_sgmef_qr.ifu_map', [
            'agency_b' => '9876543210987',
        ]);

        $resolver = new DefaultIfuResolver();

        $this->assertSame('9876543210987', $resolver->resolve('agency_b'));
    }

    public function test_resolve_falls_back_to_default_when_context_not_in_map(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', []);
        config()->set('lara_sgmef_qr.ifu_map', [
            'agency_b' => '9876543210987',
        ]);

        $resolver = new DefaultIfuResolver();

        $this->assertSame('1234567890123', $resolver->resolve('unknown_context'));
    }

    public function test_resolve_uses_first_valid_from_ifus_list(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', ['9876543210987', '1111111111111']);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $this->assertSame('9876543210987', $resolver->resolve());
    }

    public function test_resolve_falls_back_to_default_when_ifus_empty(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', []);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $this->assertSame('1234567890123', $resolver->resolve());
    }

    public function test_resolve_throws_when_no_valid_ifu(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', null);
        config()->set('lara_sgmef_qr.ifus', []);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve();
    }

    public function test_all_returns_all_configured_ifus(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', ['9876543210987', '1111111111111']);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $all = $resolver->all();

        $this->assertContains('9876543210987', $all);
        $this->assertContains('1111111111111', $all);
        $this->assertContains('1234567890123', $all);
    }

    public function test_all_deduplicates(): void
    {
        config()->set('lara_sgmef_qr.default_ifu', '1234567890123');
        config()->set('lara_sgmef_qr.ifus', ['1234567890123', '9876543210987']);
        config()->set('lara_sgmef_qr.ifu_map', []);

        $resolver = new DefaultIfuResolver();

        $all = $resolver->all();

        $this->assertCount(2, $all);
    }
}
