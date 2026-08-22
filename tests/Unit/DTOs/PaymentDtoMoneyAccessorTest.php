<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\DTOs;

use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\Enums\PaymentTypeEnum;
use Banelsems\LaraSgmefQr\Support\Money;
use Orchestra\Testbench\TestCase;

class PaymentDtoMoneyAccessorTest extends TestCase
{
    public function test_amount_money_returns_integer_xof(): void
    {
        $payment = new PaymentDto(name: 'ESPECES', amount: 1500.75);

        $money = $payment->amountMoney();

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame(1501, $money->getAmount());
    }

    public function test_amount_money_with_integer(): void
    {
        $payment = new PaymentDto(name: 'VIREMENT', amount: 2000);

        $this->assertSame(2000, $payment->amountMoney()->getAmount());
    }

    public function test_type_returns_payment_enum(): void
    {
        $payment = new PaymentDto(name: 'CARTEBANCAIRE', amount: 500);

        $this->assertSame(PaymentTypeEnum::CARTE_BANCAIRE, $payment->type());
    }
}
