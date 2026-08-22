<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Support;

use Banelsems\LaraSgmefQr\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le value object Money (XOF, integer minor units).
 */
class MoneyTest extends TestCase
{
    public function test_creates_from_int(): void
    {
        $money = Money::from(1000);

        $this->assertEquals(1000, $money->getAmount());
        $this->assertEquals(1000, $money->getMinorUnits());
    }

    public function test_creates_from_float_and_rounds(): void
    {
        $money = Money::from(1000.4);
        $this->assertEquals(1000, $money->getAmount());

        $money = Money::from(1000.5);
        $this->assertEquals(1001, $money->getAmount());
    }

    public function test_creates_from_string(): void
    {
        $money = Money::from('1500');
        $this->assertEquals(1500, $money->getAmount());
    }

    public function test_creates_from_minor_units(): void
    {
        $money = Money::fromMinorUnits(500);
        $this->assertEquals(500, $money->getAmount());
    }

    public function test_throws_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money(-100);
    }

    public function test_add(): void
    {
        $result = Money::from(1000)->add(Money::from(500));

        $this->assertEquals(1500, $result->getAmount());
    }

    public function test_subtract(): void
    {
        $result = Money::from(1000)->subtract(Money::from(300));

        $this->assertEquals(700, $result->getAmount());
    }

    public function test_subtract_throws_for_negative_result(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::from(100)->subtract(Money::from(200));
    }

    public function test_multiply_by_int(): void
    {
        $result = Money::from(1000)->multiply(3);

        $this->assertEquals(3000, $result->getAmount());
    }

    public function test_multiply_by_float_and_rounds(): void
    {
        $result = Money::from(1000)->multiply(1.18);

        $this->assertEquals(1180, $result->getAmount());
    }

    public function test_equals(): void
    {
        $this->assertTrue(Money::from(1000)->equals(Money::from(1000)));
        $this->assertFalse(Money::from(1000)->equals(Money::from(999)));
    }

    public function test_is_greater_than(): void
    {
        $this->assertTrue(Money::from(1000)->isGreaterThan(Money::from(999)));
        $this->assertFalse(Money::from(1000)->isGreaterThan(Money::from(1000)));
    }

    public function test_is_greater_than_or_equal_to(): void
    {
        $this->assertTrue(Money::from(1000)->isGreaterThanOrEqualTo(Money::from(1000)));
        $this->assertTrue(Money::from(1001)->isGreaterThanOrEqualTo(Money::from(1000)));
        $this->assertFalse(Money::from(999)->isGreaterThanOrEqualTo(Money::from(1000)));
    }

    public function test_is_less_than(): void
    {
        $this->assertTrue(Money::from(999)->isLessThan(Money::from(1000)));
        $this->assertFalse(Money::from(1000)->isLessThan(Money::from(1000)));
    }

    public function test_is_zero(): void
    {
        $this->assertTrue(Money::from(0)->isZero());
        $this->assertFalse(Money::from(1)->isZero());
    }

    public function test_to_api_payload(): void
    {
        $this->assertEquals(1000, Money::from(1000)->toApiPayload());
    }

    public function test_to_string(): void
    {
        $this->assertEquals('1000 XOF', (string) Money::from(1000));
    }

    public function test_zero_is_valid(): void
    {
        $money = new Money(0);

        $this->assertTrue($money->isZero());
        $this->assertEquals(0, $money->getAmount());
    }
}
