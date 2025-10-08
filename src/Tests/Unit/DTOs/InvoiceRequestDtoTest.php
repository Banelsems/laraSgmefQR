<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\DTOs;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour InvoiceRequestDto
 */
class InvoiceRequestDtoTest extends TestCase
{
    /**
     * Test de création depuis un tableau
     */
    public function test_can_create_from_array(): void
    {
        $data = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'aib' => 'A',
            'reference' => 'REF-001',
            'client' => [
                'name' => 'John Doe',
                'ifu' => '9876543210987',
                'contact' => '+229 12345678',
                'address' => '123 Rue Test'
            ],
            'operator' => [
                'id' => 1,
                'name' => 'Operator Test'
            ],
            'items' => [
                [
                    'name' => 'Article 1',
                    'price' => 1000,
                    'quantity' => 2,
                    'taxGroup' => 'B',
                    'code' => 'ART001'
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 2000
                ]
            ]
        ];

        $dto = InvoiceRequestDto::fromArray($data);

        $this->assertEquals('1234567890123', $dto->ifu);
        $this->assertEquals('FV', $dto->type);
        $this->assertEquals('A', $dto->aib);
        $this->assertEquals('REF-001', $dto->reference);
        $this->assertInstanceOf(ClientDto::class, $dto->client);
        $this->assertInstanceOf(OperatorDto::class, $dto->operator);
        $this->assertCount(1, $dto->items);
        $this->assertInstanceOf(InvoiceItemDto::class, $dto->items[0]);
        $this->assertCount(1, $dto->payment);
        $this->assertInstanceOf(PaymentDto::class, $dto->payment[0]);
    }

    /**
     * Test de conversion en tableau
     */
    public function test_can_convert_to_array(): void
    {
        $client = new ClientDto('9876543210987', 'John Doe', '+229 12345678', '123 Rue Test');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 2, 'B', 'ART001');
        $payment = new PaymentDto('ESPECES', 2000);

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment],
            'A',
            'REF-001'
        );

        $array = $dto->toArray();

        $this->assertEquals('1234567890123', $array['ifu']);
        $this->assertEquals('FV', $array['type']);
        $this->assertEquals('A', $array['aib']);
        $this->assertEquals('REF-001', $array['reference']);
        $this->assertIsArray($array['client']);
        $this->assertIsArray($array['operator']);
        $this->assertIsArray($array['items']);
        $this->assertIsArray($array['payment']);
    }

    /**
     * Test de calcul du montant total
     */
    public function test_can_calculate_total_amount(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $items = [
            new InvoiceItemDto('Article 1', 1000, 2, 'B'), // 2000
            new InvoiceItemDto('Article 2', 500, 3, 'A'),  // 1500
        ];
        $payment = [new PaymentDto('ESPECES', 3500)];

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            $items,
            $client,
            $operator,
            $payment
        );

        $this->assertEquals(3500, $dto->getTotalAmount());
        $this->assertEquals(3500, $dto->getTotalPaymentAmount());
    }

    /**
     * Test de validation avec données valides
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $errors = $dto->validate();
        $this->assertEmpty($errors);
    }

    /**
     * Test de validation avec IFU invalide
     */
    public function test_validation_fails_with_invalid_ifu(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        $dto = new InvoiceRequestDto(
            '123456789', // IFU invalide (trop court)
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $errors = $dto->validate();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('IFU', $errors[0]);
    }

    /**
     * Test de validation avec type invalide
     */
    public function test_validation_fails_with_invalid_type(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B');
        $payment = new PaymentDto('ESPECES', 1000);

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'XX', // Type invalide
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $errors = $dto->validate();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('type', $errors[0]);
    }

    /**
     * Test de validation avec montants incohérents
     */
    public function test_validation_fails_with_inconsistent_amounts(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $item = new InvoiceItemDto('Article 1', 1000, 1, 'B'); // Total: 1000
        $payment = new PaymentDto('ESPECES', 500); // Paiement: 500 (insuffisant)

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [$item],
            $client,
            $operator,
            [$payment]
        );

        $errors = $dto->validate();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('montant', $errors[0]);
    }

    /**
     * Test de validation sans articles
     */
    public function test_validation_fails_without_items(): void
    {
        $client = new ClientDto(null, 'John Doe');
        $operator = new OperatorDto(1, 'Operator Test');
        $payment = new PaymentDto('ESPECES', 1000);

        $dto = new InvoiceRequestDto(
            '1234567890123',
            'FV',
            [], // Pas d'articles
            $client,
            $operator,
            [$payment]
        );

        $errors = $dto->validate();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('article', $errors[0]);
    }
}
