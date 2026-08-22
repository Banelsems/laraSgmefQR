<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Enums\InvoiceTypeEnum;
use Orchestra\Testbench\TestCase;

class InvoiceRequestDtoCreditNoteTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    private function validItem(): array
    {
        return [
            'name' => 'Test item',
            'price' => 1000,
            'quantity' => 1,
            'taxGroup' => 'B',
        ];
    }

    private function validPayment(): array
    {
        return [
            'name' => 'ESPECES',
            'amount' => 1000,
        ];
    }

    private function validOperator(): array
    {
        return [
            'id' => 1,
            'name' => 'Test Operator',
        ];
    }

    public function test_fa_requires_reference(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FA',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
            // reference manquante
        ]);
    }

    public function test_ea_requires_reference(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'EA',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
            // reference manquante
        ]);
    }

    public function test_fa_with_reference_is_valid(): void
    {
        $dto = InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FA',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
            'reference' => 'original-invoice-uid-123',
        ]);

        $this->assertSame('FA', $dto->type);
        $this->assertSame('original-invoice-uid-123', $dto->reference);
    }

    public function test_ea_with_reference_is_valid(): void
    {
        $dto = InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'EA',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
            'reference' => 'original-invoice-uid-456',
        ]);

        $this->assertSame('EA', $dto->type);
        $this->assertSame('original-invoice-uid-456', $dto->reference);
    }

    public function test_fv_does_not_require_reference(): void
    {
        $dto = InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
        ]);

        $this->assertNull($dto->reference);
    }

    public function test_ev_does_not_require_reference(): void
    {
        $dto = InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'EV',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [$this->validPayment()],
        ]);

        $this->assertNull($dto->reference);
    }

    public function test_multi_payment_valid_when_total_matches(): void
    {
        $dto = InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [
                ['name' => 'ESPECES', 'amount' => 600],
                ['name' => 'VIREMENT', 'amount' => 400],
            ],
        ]);

        $this->assertCount(2, $dto->payment);
    }

    public function test_multi_payment_invalid_when_total_mismatches(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        InvoiceRequestDto::fromArray([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'items' => [$this->validItem()],
            'client' => ['name' => 'Test Client'],
            'operator' => $this->validOperator(),
            'payment' => [
                ['name' => 'ESPECES', 'amount' => 600],
                ['name' => 'VIREMENT', 'amount' => 500],
            ],
        ]);
    }
}
