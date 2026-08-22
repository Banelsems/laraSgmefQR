<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\DTOs;

use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\Support\Money;
use Orchestra\Testbench\TestCase;

class InvoiceItemDtoMoneyAccessorsTest extends TestCase
{
    public function test_price_money_returns_integer_xof(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 1000.50,
            quantity: 2,
            taxGroup: 'B'
        );

        $money = $item->priceMoney();

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(1001, $money->getAmount());
    }

    public function test_line_total_multiplies_price_by_quantity(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 500,
            quantity: 3,
            taxGroup: 'B'
        );

        $total = $item->lineTotal();

        $this->assertSame(1500, $total->getAmount());
    }

    public function test_line_total_with_decimal_quantity(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 1000,
            quantity: 0.5,
            taxGroup: 'B'
        );

        $total = $item->lineTotal();

        $this->assertSame(500, $total->getAmount());
    }

    public function test_tax_specific_money_returns_zero_when_null(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 1000,
            quantity: 1,
            taxGroup: 'B'
        );

        $this->assertTrue($item->taxSpecificMoney()->isZero());
    }

    public function test_tax_specific_money_returns_value_when_set(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 1000,
            quantity: 1,
            taxGroup: 'B',
            taxSpecific: 50
        );

        $this->assertSame(50, $item->taxSpecificMoney()->getAmount());
    }

    public function test_original_price_money_returns_null_when_absent(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 1000,
            quantity: 1,
            taxGroup: 'B'
        );

        $this->assertNull($item->originalPriceMoney());
    }

    public function test_original_price_money_returns_value_when_set(): void
    {
        $item = new InvoiceItemDto(
            name: 'Test item',
            price: 800,
            quantity: 1,
            taxGroup: 'B',
            originalPrice: 1000
        );

        $this->assertSame(1000, $item->originalPriceMoney()->getAmount());
    }
}
