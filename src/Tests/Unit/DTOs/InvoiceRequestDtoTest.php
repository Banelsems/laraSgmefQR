<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\DTOs;

use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class InvoiceRequestDtoTest extends TestCase
{
    /** @test */
    public function it_can_be_created_from_valid_data()
    {
        $data = $this->getValidData();

        $dto = InvoiceRequestDto::from($data);

        $this->assertEquals('1234567890123', $dto->ifu);
        $this->assertEquals('FV', $dto->type);
        $this->assertInstanceOf(ClientDto::class, $dto->client);
        $this->assertInstanceOf(OperatorDto::class, $dto->operator);
        $this->assertInstanceOf(DataCollection::class, $dto->items);
        $this->assertInstanceOf(InvoiceItemDto::class, $dto->items->first());
        $this->assertInstanceOf(DataCollection::class, $dto->payment);
        $this->assertInstanceOf(PaymentDto::class, $dto->payment->first());
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_ifu()
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::from($this->getValidData(['ifu' => 'invalid-ifu']));
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_type()
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::from($this->getValidData(['type' => 'INVALID']));
    }

    /** @test */
    public function it_throws_validation_exception_when_items_is_empty()
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::from($this->getValidData(['items' => []]));
    }

    /** @test */
    public function it_throws_validation_exception_when_payment_is_empty()
    {
        $this->expectException(ValidationException::class);

        InvoiceRequestDto::from($this->getValidData(['payment' => []]));
    }

    /** @test */
    public function it_throws_validation_exception_for_mismatched_totals()
    {
        $this->expectException(ValidationException::class);

        $data = $this->getValidData([
            'payment' => [
                ['name' => 'ESPECES', 'amount' => 100] // Montant incorrect
            ]
        ]);

        InvoiceRequestDto::from($data);
    }

    private function getValidData(array $overrides = []): array
    {
        $default = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => ['name' => 'Test Client'],
            'operator' => ['id' => 1, 'name' => 'Test Operator'],
            'items' => [
                ['name' => 'Test Item', 'price' => 1000, 'quantity' => 2, 'taxGroup' => 'B']
            ],
            'payment' => [
                ['name' => 'ESPECES', 'amount' => 2000]
            ],
        ];

        return array_merge($default, $overrides);
    }
}

