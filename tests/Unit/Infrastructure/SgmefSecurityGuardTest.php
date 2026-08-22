<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefSecurityGuard;
use Illuminate\Support\Facades\RateLimiter;
use Orchestra\Testbench\TestCase;

class SgmefSecurityGuardTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear(SgmefSecurityGuard::RATE_LIMIT_KEY);
    }

    public function test_validate_token_accepts_jwt_format(): void
    {
        $guard = new SgmefSecurityGuard(60);

        $guard->validateToken('eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.abc123sign');

        $this->assertTrue(true); // No exception thrown
    }

    public function test_validate_token_accepts_long_opaque_token(): void
    {
        $guard = new SgmefSecurityGuard(60);

        $guard->validateToken(str_repeat('a', 25));

        $this->assertTrue(true);
    }

    public function test_validate_token_rejects_empty_token(): void
    {
        $guard = new SgmefSecurityGuard(60);

        $this->expectException(SgmefAuthenticationException::class);
        $this->expectExceptionMessage('Token e-MECeF manquant ou vide');

        $guard->validateToken('');
    }

    public function test_validate_token_rejects_whitespace_only_token(): void
    {
        $guard = new SgmefSecurityGuard(60);

        $this->expectException(SgmefAuthenticationException::class);
        $this->expectExceptionMessage('manquant ou vide');

        $guard->validateToken('   ');
    }

    public function test_validate_token_rejects_short_token(): void
    {
        $guard = new SgmefSecurityGuard(60);

        $this->expectException(SgmefAuthenticationException::class);
        $this->expectExceptionMessage('mal formé');

        $guard->validateToken('short');
    }

    public function test_enforce_rate_limit_allows_under_limit(): void
    {
        $guard = new SgmefSecurityGuard(10);

        for ($i = 0; $i < 5; $i++) {
            $guard->enforceRateLimit();
        }

        $this->assertTrue(true); // No exception
    }

    public function test_enforce_rate_limit_blocks_over_limit(): void
    {
        $guard = new SgmefSecurityGuard(3);

        $guard->enforceRateLimit();
        $guard->enforceRateLimit();
        $guard->enforceRateLimit();

        $this->expectException(SgmefAuthenticationException::class);
        $this->expectExceptionMessage('Limite de taux');

        $guard->enforceRateLimit();
    }

    public function test_enforce_rate_limit_disabled_when_zero(): void
    {
        $guard = new SgmefSecurityGuard(0);

        for ($i = 0; $i < 100; $i++) {
            $guard->enforceRateLimit();
        }

        $this->assertTrue(true); // No exception
    }
}
