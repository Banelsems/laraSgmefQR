<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Illuminate\Validation\ValidationException;
use Orchestra\Testbench\TestCase;

/**
 * Tests de la source de vérité unique de validation.
 *
 * Vérifie qu'une même entrée ne peut pas être acceptée par une couche
 * mais rejetée par une autre. Depuis v4.0, la validation est centralisée
 * dans InvoiceRequestDto::fromArray().
 */
class ValidationSingleSourceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_valid_data_passes_validation(): void
    {
        $dto = InvoiceRequestDto::fromArray($this->validData());

        $this->assertEquals('1234567890123', $dto->ifu);
        $this->assertEquals('FV', $dto->type);
    }

    public function test_mismatched_totals_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'payment' => [['name' => 'ESPECES', 'amount' => 100]],
        ]));
    }

    public function test_fa_without_reference_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'type' => 'FA',
        ]));
    }

    public function test_fa_with_reference_accepted(): void
    {
        $dto = InvoiceRequestDto::fromArray($this->validData([
            'type' => 'FA',
            'reference' => 'original-uid-12345',
        ]));

        $this->assertEquals('FA', $dto->type);
        $this->assertEquals('original-uid-12345', $dto->reference);
    }

    public function test_ea_without_reference_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'type' => 'EA',
        ]));
    }

    public function test_empty_client_name_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'client' => ['name' => ''],
        ]));
    }

    public function test_invalid_payment_type_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'payment' => [['name' => 'BITCOIN', 'amount' => 2000]],
        ]));
    }

    public function test_invalid_tax_group_rejected(): void
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::fromArray($this->validData([
            'items' => [['name' => 'Item', 'price' => 1000, 'quantity' => 2, 'taxGroup' => 'Z']],
            'payment' => [['name' => 'ESPECES', 'amount' => 2000]],
        ]));
    }

    public function test_decimal_quantities_accepted_with_correct_totals(): void
    {
        $dto = InvoiceRequestDto::fromArray($this->validData([
            'items' => [['name' => 'Item', 'price' => 1000, 'quantity' => 1.5, 'taxGroup' => 'B']],
            'payment' => [['name' => 'ESPECES', 'amount' => 1500]],
        ]));

        $this->assertEquals(1.5, $dto->items->first()->quantity);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => ['name' => 'Test Client'],
            'operator' => ['id' => 1, 'name' => 'Test Operator'],
            'items' => [
                ['name' => 'Test Item', 'price' => 1000, 'quantity' => 2, 'taxGroup' => 'B'],
            ],
            'payment' => [
                ['name' => 'ESPECES', 'amount' => 2000],
            ],
        ], $overrides);
    }
}
