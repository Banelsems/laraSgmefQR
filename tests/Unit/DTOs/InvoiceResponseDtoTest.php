<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\DTOs;

use Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto;
use PHPUnit\Framework\TestCase;

class InvoiceResponseDtoTest extends TestCase
{
    /**
     * @test
     * @covers \Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto::fromArray
     */
    public function it_can_be_created_from_api_response_with_short_keys()
    {
        // 1. Simuler la réponse JSON de l'API avec les clés courtes
        $apiResponseData = [
            'uid' => 'f3ef8d4a-c8ca-4212-a3bc-69238ac38d86',
            'total' => 4000.0,
            'ts' => 610.0, // Total taxes
            'aib' => 0.0,
            'status' => 'pending',
            'date' => '2025-10-19T10:00:00Z',
        ];

        // 2. Créer le DTO à partir de ces données
        $dto = InvoiceResponseDto::fromArray($apiResponseData);

        // 3. Vérifier que les propriétés du DTO ont été correctement mappées
        $this->assertSame('f3ef8d4a-c8ca-4212-a3bc-69238ac38d86', $dto->uid);
        $this->assertSame(4000.0, $dto->totalAmount);
        $this->assertSame(610.0, $dto->totalTaxAmount);
        $this->assertSame(0.0, $dto->totalAibAmount);
        $this->assertSame('pending', $dto->status);
        $this->assertSame('2025-10-19T10:00:00Z', $dto->dateTime);
    }

    /**
     * @test
     * @covers \Banelsems\LaraSgmefQr\DTOs\InvoiceResponseDto::fromArray
     */
    public function it_handles_missing_optional_keys_gracefully()
    {
        // Simuler une réponse minimale
        $apiResponseData = [
            'uid' => 'test-uid',
            'total' => 100.0,
        ];

        $dto = InvoiceResponseDto::fromArray($apiResponseData);

        $this->assertSame(100.0, $dto->totalAmount);
        $this->assertNull($dto->totalTaxAmount);
        $this->assertNull($dto->totalAibAmount);
        $this->assertNull($dto->status);
    }
}
