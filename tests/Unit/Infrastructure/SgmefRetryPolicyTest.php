<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNetworkException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefRateLimitException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefServerException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefTimeoutException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefValidationException;
use Banelsems\LaraSgmefQr\Infrastructure\SgmefRetryPolicy;
use Orchestra\Testbench\TestCase;

class SgmefRetryPolicyTest extends TestCase
{
    private SgmefRetryPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new SgmefRetryPolicy(maxAttempts: 2, baseDelayMs: 100);
    }

    // --- Idempotence classification ---

    public function test_get_status_is_retryable(): void
    {
        $this->assertTrue($this->policy->isRetryable('GET', '/info/status'));
    }

    public function test_get_tax_groups_is_retryable(): void
    {
        $this->assertTrue($this->policy->isRetryable('GET', '/info/taxGroups'));
    }

    public function test_get_invoice_is_retryable(): void
    {
        $this->assertTrue($this->policy->isRetryable('GET', '/invoice/uid-123'));
    }

    public function test_post_invoice_is_not_retryable(): void
    {
        $this->assertFalse($this->policy->isRetryable('POST', '/invoice'));
    }

    public function test_put_confirm_is_not_retryable(): void
    {
        $this->assertFalse($this->policy->isRetryable('PUT', '/invoice/uid-123/confirm'));
    }

    public function test_put_cancel_is_not_retryable(): void
    {
        $this->assertFalse($this->policy->isRetryable('PUT', '/invoice/uid-123/cancel'));
    }

    // --- shouldRetry ---

    public function test_should_retry_get_on_network_error(): void
    {
        $exception = new SgmefNetworkException('network error');

        $this->assertTrue($this->policy->shouldRetry('GET', '/info/status', $exception, 1));
    }

    public function test_should_not_retry_post_on_network_error(): void
    {
        $exception = new SgmefNetworkException('network error');

        $this->assertFalse($this->policy->shouldRetry('POST', '/invoice', $exception, 1));
    }

    public function test_should_not_retry_on_validation_error(): void
    {
        $exception = new SgmefValidationException('bad request', 0, null, null, 422);

        // Même sur GET, une validation error n'est pas transitoire
        $this->assertFalse($this->policy->shouldRetry('GET', '/info/status', $exception, 1));
    }

    public function test_should_not_retry_on_auth_error(): void
    {
        $exception = new SgmefAuthenticationException('unauthorized', 0, null, null, 401);

        $this->assertFalse($this->policy->shouldRetry('GET', '/info/status', $exception, 1));
    }

    public function test_should_retry_on_server_error(): void
    {
        $exception = new SgmefServerException('server error', 0, null, null, 500);

        $this->assertTrue($this->policy->shouldRetry('GET', '/info/status', $exception, 1));
    }

    public function test_should_retry_on_timeout(): void
    {
        $exception = new SgmefTimeoutException('timeout');

        $this->assertTrue($this->policy->shouldRetry('GET', '/info/status', $exception, 1));
    }

    public function test_should_not_retry_after_max_attempts(): void
    {
        $exception = new SgmefNetworkException('network error');

        // Attempt 2 avec maxAttempts=2 → pas de retry
        $this->assertFalse($this->policy->shouldRetry('GET', '/info/status', $exception, 2));
    }

    // --- delayFor ---

    public function test_delay_uses_exponential_backoff(): void
    {
        $exception = new SgmefNetworkException('error');

        // Attempt 1: base * 2^0 = 100
        $this->assertSame(100, $this->policy->delayFor($exception, 1));
        // Attempt 2: base * 2^1 = 200
        $this->assertSame(200, $this->policy->delayFor($exception, 2));
    }

    public function test_delay_respects_retry_after_for_rate_limit(): void
    {
        $exception = new SgmefRateLimitException('rate', 0, null, null, 429, null, [], 10);

        $this->assertSame(10000, $this->policy->delayFor($exception, 1));
    }

    public function test_delay_caps_retry_after_at_30_seconds(): void
    {
        $exception = new SgmefRateLimitException('rate', 0, null, null, 429, null, [], 120);

        // 120s → capped at 30s = 30000ms
        $this->assertSame(30000, $this->policy->delayFor($exception, 1));
    }

    public function test_delay_uses_backoff_when_no_retry_after(): void
    {
        $exception = new SgmefRateLimitException('rate', 0, null, null, 429);

        // Pas de Retry-After → backoff normal
        $this->assertSame(100, $this->policy->delayFor($exception, 1));
    }
}
