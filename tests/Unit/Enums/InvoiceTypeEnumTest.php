<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Enums;

use Banelsems\LaraSgmefQr\Enums\InvoiceTypeEnum;
use Banelsems\LaraSgmefQr\Enums\PaymentTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour PaymentTypeEnum et InvoiceTypeEnum.
 */
class PaymentTypeEnumTest extends TestCase
{
    public function test_values_returns_all_payment_types(): void
    {
        $values = PaymentTypeEnum::values();

        $this->assertContains('ESPECES', $values);
        $this->assertContains('VIREMENT', $values);
        $this->assertContains('CARTEBANCAIRE', $values);
        $this->assertContains('MOBILEMONEY', $values);
        $this->assertContains('CHEQUES', $values);
        $this->assertContains('CREDIT', $values);
        $this->assertContains('AUTRE', $values);
        $this->assertCount(7, $values);
    }

    public function test_label(): void
    {
        $this->assertEquals('Espèces', PaymentTypeEnum::ESPECES->label());
        $this->assertEquals('Carte bancaire', PaymentTypeEnum::CARTE_BANCAIRE->label());
        $this->assertEquals('Mobile Money', PaymentTypeEnum::MOBILE_MONEY->label());
    }

    public function test_from_value(): void
    {
        $this->assertEquals(PaymentTypeEnum::ESPECES, PaymentTypeEnum::from('ESPECES'));
        $this->assertEquals(PaymentTypeEnum::CHEQUES, PaymentTypeEnum::from('CHEQUES'));
    }
}

class InvoiceTypeEnumTest extends TestCase
{
    public function test_values_returns_all_invoice_types(): void
    {
        $values = InvoiceTypeEnum::values();

        $this->assertContains('FV', $values);
        $this->assertContains('FA', $values);
        $this->assertContains('EV', $values);
        $this->assertContains('EA', $values);
        $this->assertCount(4, $values);
    }

    public function test_is_credit_note(): void
    {
        $this->assertTrue(InvoiceTypeEnum::FA->isCreditNote());
        $this->assertTrue(InvoiceTypeEnum::EA->isCreditNote());
        $this->assertFalse(InvoiceTypeEnum::FV->isCreditNote());
        $this->assertFalse(InvoiceTypeEnum::EV->isCreditNote());
    }

    public function test_original_type(): void
    {
        $this->assertEquals(InvoiceTypeEnum::FV, InvoiceTypeEnum::FA->originalType());
        $this->assertEquals(InvoiceTypeEnum::EV, InvoiceTypeEnum::EA->originalType());
        $this->assertEquals(InvoiceTypeEnum::FV, InvoiceTypeEnum::FV->originalType());
        $this->assertEquals(InvoiceTypeEnum::EV, InvoiceTypeEnum::EV->originalType());
    }

    public function test_label(): void
    {
        $this->assertEquals('Facture de Vente', InvoiceTypeEnum::FV->label());
        $this->assertEquals('Facture d\'Avoir', InvoiceTypeEnum::FA->label());
    }
}
