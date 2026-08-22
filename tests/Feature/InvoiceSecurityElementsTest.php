<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\DTOs\SecurityElementsDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceTypeEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Orchestra\Testbench\TestCase;

class InvoiceSecurityElementsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_security_elements_returns_null_when_empty(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = null;

        $this->assertNull($invoice->securityElements());
    }

    public function test_security_elements_returns_dto_when_present(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = [
            'dateTime' => '2024-01-01T12:00:00+00:00',
            'qrCode' => 'qr-data-123',
            'codeMECeFDGI' => 'MECF123',
            'counters' => 'CTR456',
            'nim' => 'NIM789',
            'errorCode' => 'OK',
        ];

        $dto = $invoice->securityElements();

        $this->assertInstanceOf(SecurityElementsDto::class, $dto);
        $this->assertSame('qr-data-123', $dto->qrCode);
        $this->assertSame('MECF123', $dto->codeMECeFDGI);
        $this->assertSame('NIM789', $dto->nim);
    }

    public function test_qr_code_payload_returns_data(): void
    {
        $invoice = new Invoice();
        $invoice->qr_code_data = 'qr-payload-123';

        $this->assertSame('qr-payload-123', $invoice->qrCodePayload());
    }

    public function test_has_qr_code_true_when_data_present(): void
    {
        $invoice = new Invoice();
        $invoice->qr_code_data = 'qr-data';

        $this->assertTrue($invoice->hasQrCode());
    }

    public function test_has_qr_code_false_when_data_absent(): void
    {
        $invoice = new Invoice();
        $invoice->qr_code_data = null;

        $this->assertFalse($invoice->hasQrCode());
    }

    public function test_mecf_code_returns_value(): void
    {
        $invoice = new Invoice();
        $invoice->mecf_code = 'MECF-2024-001';

        $this->assertSame('MECF-2024-001', $invoice->mecfCode());
    }

    public function test_nim_returns_value_from_security_elements(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = ['nim' => 'NIM-123'];

        $this->assertSame('NIM-123', $invoice->nim());
    }

    public function test_nim_returns_null_without_security_elements(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = null;

        $this->assertNull($invoice->nim());
    }

    public function test_mecf_date_time_from_security_elements(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = ['dateTime' => '2024-06-15T10:30:00+00:00'];

        $this->assertSame('2024-06-15T10:30:00+00:00', $invoice->mecfDateTime());
    }

    public function test_counters_from_security_elements(): void
    {
        $invoice = new Invoice();
        $invoice->security_elements = ['counters' => 'CTR-001'];

        $this->assertSame('CTR-001', $invoice->counters());
    }

    public function test_invoice_type_returns_enum(): void
    {
        $invoice = new Invoice();
        $invoice->type = 'FV';

        $this->assertSame(InvoiceTypeEnum::FV, $invoice->invoiceType());
    }

    public function test_is_credit_note_true_for_fa(): void
    {
        $invoice = new Invoice();
        $invoice->type = 'FA';

        $this->assertTrue($invoice->isCreditNote());
    }

    public function test_is_credit_note_true_for_ea(): void
    {
        $invoice = new Invoice();
        $invoice->type = 'EA';

        $this->assertTrue($invoice->isCreditNote());
    }

    public function test_is_credit_note_false_for_fv(): void
    {
        $invoice = new Invoice();
        $invoice->type = 'FV';

        $this->assertFalse($invoice->isCreditNote());
    }
}
