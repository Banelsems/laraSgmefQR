<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Contracts\SleeperInterface;
use Banelsems\LaraSgmefQr\Infrastructure\NativeSleeper;
use Banelsems\LaraSgmefQr\Tests\Support\FakeSleeper;
use Orchestra\Testbench\TestCase;

class SleeperTest extends TestCase
{
    public function test_fake_sleeper_records_sleeps(): void
    {
        $sleeper = new FakeSleeper();

        $sleeper->sleepMilliseconds(500);
        $sleeper->sleepMilliseconds(1000);

        $this->assertSame([500, 1000], $sleeper->sleeps);
        $this->assertSame(1500, $sleeper->totalSleptMilliseconds());
    }

    public function test_fake_sleeper_reset(): void
    {
        $sleeper = new FakeSleeper();
        $sleeper->sleepMilliseconds(500);
        $sleeper->reset();

        $this->assertSame([], $sleeper->sleeps);
    }

    public function test_native_sleeper_implements_interface(): void
    {
        $sleeper = new NativeSleeper();

        $this->assertInstanceOf(SleeperInterface::class, $sleeper);
    }

    public function test_native_sleeper_zero_does_not_sleep(): void
    {
        $sleeper = new NativeSleeper();

        // Ne doit pas lever d'exception pour 0
        $sleeper->sleepMilliseconds(0);

        $this->assertTrue(true);
    }
}
