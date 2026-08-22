<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use Banelsems\LaraSgmefQr\DTOs\VerificationResultDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Banelsems\LaraSgmefQr\Services\QrCodeService;
use Banelsems\LaraSgmefQr\Services\VerificationService;
use Orchestra\Testbench\TestCase;

class VerificationServiceTest extends TestCase
{
    private VerificationService $service;

    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VerificationService(new QrCodeService());
    }

    private function createConfirmedInvoice(array $overrides = []): Invoice
    {
        $invoice = new Invoice();
        $invoice->uid = $overrides['uid'] ?? 'test-uid-12345';
        $invoice->status = $overrides['status'] ?? InvoiceStatusEnum::CONFIRMED;
        $invoice->qr_code_data = $overrides['qr_code_data'] ?? 'test-uid-12345';
        $invoice->mecf_code = $overrides['mecf_code'] ?? 'MECF123456';
        $invoice->security_elements = $overrides['security_elements'] ?? [
            'dateTime' => '2024-01-01T12:00:00+00:00',
            'qrCode' => 'test-uid-12345',
            'codeMECeFDGI' => 'MECF123456',
            'counters' => 'CTR789',
            'nim' => 'NIM000111',
            'errorCode' => 'OK',
        ];

        return $invoice;
    }

    public function test_verify_returns_valid_for_complete_confirmed_invoice(): void
    {
        $invoice = $this->createConfirmedInvoice();

        $result = $this->service->verify($invoice);

        $this->assertInstanceOf(VerificationResultDto::class, $result);
        $this->assertTrue($result->valid);
        $this->assertEmpty($result->issues);
    }

    public function test_verify_returns_all_checks_passed(): void
    {
        $invoice = $this->createConfirmedInvoice();

        $result = $this->service->verify($invoice);

        $this->assertTrue($result->checkPassed('invoice_confirmed'));
        $this->assertTrue($result->checkPassed('uid_present'));
        $this->assertTrue($result->checkPassed('qr_code_present'));
        $this->assertTrue($result->checkPassed('qr_code_valid'));
        $this->assertTrue($result->checkPassed('mecf_code_present'));
        $this->assertTrue($result->checkPassed('mecf_datetime_present'));
        $this->assertTrue($result->checkPassed('counters_present'));
        $this->assertTrue($result->checkPassed('nim_present'));
    }

    public function test_verify_returns_verification_url(): void
    {
        $invoice = $this->createConfirmedInvoice();

        $result = $this->service->verify($invoice);

        $this->assertNotNull($result->verificationUrl);
        $this->assertStringContainsString('verification', $result->verificationUrl);
        $this->assertStringContainsString('test-uid-12345', $result->verificationUrl);
    }

    public function test_verify_fails_for_pending_invoice(): void
    {
        $invoice = $this->createConfirmedInvoice([
            'status' => InvoiceStatusEnum::PENDING,
        ]);

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('invoice_confirmed'));
        $this->assertNotEmpty($result->issues);
    }

    public function test_verify_fails_when_uid_missing(): void
    {
        $invoice = $this->createConfirmedInvoice();
        $invoice->uid = null;
        // QR code won't contain null UID, so qr_code_valid will also fail
        $invoice->qr_code_data = 'some-data-without-uid';

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('uid_present'));
    }

    public function test_verify_fails_when_qr_code_missing(): void
    {
        $invoice = $this->createConfirmedInvoice();
        $invoice->qr_code_data = null;

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('qr_code_present'));
        $this->assertNull($result->verificationUrl);
    }

    public function test_verify_fails_when_qr_code_invalid(): void
    {
        $invoice = $this->createConfirmedInvoice();
        $invoice->qr_code_data = 'completely-different-data';
        $invoice->mecf_code = 'DIFFERENTCODE';

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('qr_code_valid'));
    }

    public function test_verify_fails_when_mecf_code_missing(): void
    {
        $invoice = $this->createConfirmedInvoice();
        $invoice->mecf_code = null;

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('mecf_code_present'));
    }

    public function test_verify_fails_when_security_elements_incomplete(): void
    {
        $invoice = $this->createConfirmedInvoice([
            'security_elements' => [
                'dateTime' => '2024-01-01T12:00:00+00:00',
                'qrCode' => 'test-uid-12345',
                'codeMECeFDGI' => 'MECF123456',
                // Missing: counters, nim
            ],
        ]);

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertFalse($result->checkPassed('counters_present'));
        $this->assertFalse($result->checkPassed('nim_present'));
    }

    public function test_verify_collects_all_issues(): void
    {
        $invoice = new Invoice();
        $invoice->uid = null;
        $invoice->status = InvoiceStatusEnum::PENDING;
        $invoice->qr_code_data = null;
        $invoice->mecf_code = null;
        $invoice->security_elements = null;

        $result = $this->service->verify($invoice);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->issues);
        // Should have issues for: not confirmed, uid missing, qr missing, mecf missing,
        // datetime missing, counters missing, nim missing
        $this->assertGreaterThan(4, count($result->issues));
    }
}
