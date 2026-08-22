<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Infrastructure\SgmefLogSanitizer;
use Orchestra\Testbench\TestCase;

class SgmefLogSanitizerTest extends TestCase
{
    private SgmefLogSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new SgmefLogSanitizer();
    }

    public function test_redacts_authorization_header(): void
    {
        $result = $this->sanitizer->sanitize([
            'Authorization' => 'Bearer SECRET-TOKEN',
            'method' => 'POST',
        ]);

        $this->assertSame('[REDACTED]', $result['Authorization']);
        $this->assertSame('POST', $result['method']);
    }

    public function test_redacts_token(): void
    {
        $result = $this->sanitizer->sanitize([
            'token' => 'abc123',
        ]);

        $this->assertSame('[REDACTED]', $result['token']);
    }

    public function test_redacts_password(): void
    {
        $result = $this->sanitizer->sanitize([
            'password' => 'secret-pass',
        ]);

        $this->assertSame('[REDACTED]', $result['password']);
    }

    public function test_redacts_case_insensitive(): void
    {
        $result = $this->sanitizer->sanitize([
            'TOKEN' => 'upper-token',
            'Token' => 'mixed-token',
            'API_KEY' => 'key-value',
        ]);

        $this->assertSame('[REDACTED]', $result['TOKEN']);
        $this->assertSame('[REDACTED]', $result['Token']);
        $this->assertSame('[REDACTED]', $result['API_KEY']);
    }

    public function test_redacts_nested_arrays(): void
    {
        $result = $this->sanitizer->sanitize([
            'outer' => [
                'inner' => [
                    'password' => 'nested-pass',
                    'safe_key' => 'safe-value',
                ],
            ],
        ]);

        $this->assertSame('[REDACTED]', $result['outer']['inner']['password']);
        $this->assertSame('safe-value', $result['outer']['inner']['safe_key']);
    }

    public function test_redacts_partial_match(): void
    {
        $result = $this->sanitizer->sanitize([
            'x-authorization' => 'bearer xxx',
            'my_token' => 'yyy',
            'user_password_hash' => 'zzz',
        ]);

        $this->assertSame('[REDACTED]', $result['x-authorization']);
        $this->assertSame('[REDACTED]', $result['my_token']);
        $this->assertSame('[REDACTED]', $result['user_password_hash']);
    }

    public function test_preserves_non_sensitive_keys(): void
    {
        $result = $this->sanitizer->sanitize([
            'method' => 'GET',
            'endpoint' => '/info/status',
            'status' => 200,
            'duration_ms' => 150,
        ]);

        $this->assertSame('GET', $result['method']);
        $this->assertSame('/info/status', $result['endpoint']);
        $this->assertSame(200, $result['status']);
        $this->assertSame(150, $result['duration_ms']);
    }

    public function test_empty_array(): void
    {
        $result = $this->sanitizer->sanitize([]);

        $this->assertSame([], $result);
    }

    public function test_all_sensitive_keys(): void
    {
        $input = [
            'authorization' => 'a',
            'token' => 'b',
            'api_token' => 'c',
            'access_token' => 'd',
            'refresh_token' => 'e',
            'password' => 'f',
            'secret' => 'g',
            'credentials' => 'h',
            'api_key' => 'i',
            'apikey' => 'j',
            'private_key' => 'k',
            'bearer' => 'l',
        ];

        $result = $this->sanitizer->sanitize($input);

        foreach ($result as $value) {
            $this->assertSame('[REDACTED]', $value);
        }
    }
}
