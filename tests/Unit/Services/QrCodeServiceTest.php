<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\Services\QrCodeService;
use Orchestra\Testbench\TestCase;

class QrCodeServiceTest extends TestCase
{
    private QrCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QrCodeService();
    }

    public function test_svg_generates_valid_svg_with_qr_code(): void
    {
        $svg = $this->service->svg('test-payload-123', 200);

        $this->assertStringStartsWith('<?xml', $svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('width="200"', $svg);
        $this->assertStringContainsString('height="200"', $svg);
    }

    public function test_svg_contains_qr_modules_not_text(): void
    {
        $svg = $this->service->svg('MECF123456', 300);

        // Un vrai QR code contient des rectangles/modules, pas du texte
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringNotContainsString('<text', $svg);
    }

    public function test_svg_with_different_sizes(): void
    {
        $svg100 = $this->service->svg('test', 100);
        $svg400 = $this->service->svg('test', 400);

        $this->assertStringContainsString('width="100"', $svg100);
        $this->assertStringContainsString('width="400"', $svg400);
    }

    public function test_svg_encodes_payload(): void
    {
        $payload = 'https://verify.impots.bj?q=MECF-2024-001';
        $svg = $this->service->svg($payload, 200);

        // Le QR code doit être généré sans erreur pour un payload complexe
        $this->assertStringStartsWith('<?xml', $svg);
    }

    public function test_validate_invoice_qr_returns_false_without_data(): void
    {
        $invoice = new \Banelsems\LaraSgmefQr\Models\Invoice();
        $invoice->uid = null;
        $invoice->qr_code_data = null;

        $this->assertFalse($this->service->validateInvoiceQr($invoice));
    }

    public function test_validate_invoice_qr_returns_true_when_uid_in_payload(): void
    {
        $invoice = new \Banelsems\LaraSgmefQr\Models\Invoice();
        $invoice->uid = 'test-uid-123';
        $invoice->qr_code_data = 'some-data-containing-test-uid-123-here';

        $this->assertTrue($this->service->validateInvoiceQr($invoice));
    }

    public function test_validate_invoice_qr_returns_true_when_mecf_code_in_payload(): void
    {
        $invoice = new \Banelsems\LaraSgmefQr\Models\Invoice();
        $invoice->uid = 'test-uid-123';
        $invoice->mecf_code = 'MECF456';
        $invoice->qr_code_data = 'data-with-MECF456-inside';

        $this->assertTrue($this->service->validateInvoiceQr($invoice));
    }

    public function test_verification_url_throws_without_qr_data(): void
    {
        $invoice = new \Banelsems\LaraSgmefQr\Models\Invoice();
        $invoice->qr_code_data = null;

        $this->expectException(\InvalidArgumentException::class);
        $this->service->verificationUrl($invoice);
    }

    public function test_verification_url_builds_correct_url(): void
    {
        config()->set('lara_sgmef_qr.verification_url', 'https://verify.impots.bj/check');

        $invoice = new \Banelsems\LaraSgmefQr\Models\Invoice();
        $invoice->qr_code_data = 'MECF-2024-001';

        $url = $this->service->verificationUrl($invoice);

        $this->assertSame('https://verify.impots.bj/check?q=MECF-2024-001', $url);
    }

    public function test_png_generates_real_png_when_gd_available(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('ext-gd non disponible — test PNG ignoré.');
        }

        $png = $this->service->png('test-payload-png', 200);

        // Signature magique PNG : 89 50 4E 47 0D 0A 1A 0A
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
        // Ne doit jamais contenir du SVG
        $this->assertStringNotContainsString('<svg', $png);
        $this->assertStringNotContainsString('<?xml', $png);
    }

    public function test_png_throws_explicit_exception_when_gd_missing(): void
    {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('ext-gd disponible — test exception ignoré.');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ext-gd');

        $this->service->png('test', 200);
    }
}
