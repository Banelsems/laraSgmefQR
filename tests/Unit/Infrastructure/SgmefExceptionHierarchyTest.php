<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefAuthenticationException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNetworkException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefNotFoundException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefRateLimitException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefServerException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefTimeoutException;
use Banelsems\LaraSgmefQr\Exceptions\SgmefValidationException;
use Orchestra\Testbench\TestCase;

class SgmefExceptionHierarchyTest extends TestCase
{
    public function test_all_subclasses_extend_base_exception(): void
    {
        $this->assertTrue(is_subclass_of(SgmefAuthenticationException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefValidationException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefRateLimitException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefTimeoutException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefNetworkException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefServerException::class, SgmefApiException::class));
        $this->assertTrue(is_subclass_of(SgmefNotFoundException::class, SgmefApiException::class));
    }

    public function test_all_subclasses_catchable_via_base(): void
    {
        $exceptions = [
            new SgmefAuthenticationException('auth'),
            new SgmefValidationException('validation'),
            new SgmefRateLimitException('rate'),
            new SgmefTimeoutException('timeout'),
            new SgmefNetworkException('network'),
            new SgmefServerException('server'),
            new SgmefNotFoundException('not found'),
        ];

        foreach ($exceptions as $e) {
            try {
                throw $e;
            } catch (SgmefApiException $caught) {
                // OK : toutes les sous-classes sont catchables via la base
                $this->assertSame($e, $caught);
            }
        }
    }

    public function test_base_exception_stores_structured_data(): void
    {
        $e = new SgmefApiException(
            'test message',
            500,
            null,
            ['key' => 'value'],
            404,
            'ERR_001',
            ['context_key' => 'context_value']
        );

        $this->assertSame(['key' => 'value'], $e->getApiResponse());
        $this->assertSame(404, $e->httpStatus());
        $this->assertSame('ERR_001', $e->errorCode());
        $this->assertSame(['context_key' => 'context_value'], $e->context());
    }

    public function test_is_authentication_error(): void
    {
        $e = new SgmefAuthenticationException('auth', 0, null, null, 401);
        $this->assertTrue($e->isAuthenticationError());
        $this->assertFalse($e->isValidationError());
    }

    public function test_is_validation_error(): void
    {
        $e = new SgmefValidationException('validation', 0, null, null, 422);
        $this->assertTrue($e->isValidationError());
        $this->assertFalse($e->isAuthenticationError());
    }

    public function test_is_server_error(): void
    {
        $e = new SgmefServerException('server', 0, null, null, 500);
        $this->assertTrue($e->isServerError());
    }

    public function test_is_rate_limit_error(): void
    {
        $e = new SgmefRateLimitException('rate', 0, null, null, 429);
        $this->assertTrue($e->isRateLimitError());
    }

    public function test_is_network_error(): void
    {
        $e = new SgmefNetworkException('network');
        $this->assertTrue($e->isNetworkError());

        $nonNetwork = new SgmefServerException('server');
        $this->assertFalse($nonNetwork->isNetworkError());
    }

    public function test_rate_limit_exception_stores_retry_after(): void
    {
        $e = new SgmefRateLimitException('rate', 0, null, null, 429, null, [], 30);
        $this->assertSame(30, $e->retryAfter());
    }

    public function test_rate_limit_exception_without_retry_after(): void
    {
        $e = new SgmefRateLimitException('rate');
        $this->assertNull($e->retryAfter());
    }
}
