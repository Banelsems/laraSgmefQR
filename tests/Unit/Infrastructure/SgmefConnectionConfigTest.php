<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Infrastructure;

use Banelsems\LaraSgmefQr\Infrastructure\SgmefConnectionConfig;
use Orchestra\Testbench\TestCase;

class SgmefConnectionConfigTest extends TestCase
{
    public function test_construct_with_all_params(): void
    {
        $config = new SgmefConnectionConfig(
            'https://api.example.com',
            'my-token',
            60,
            15,
            false
        );

        $this->assertSame('https://api.example.com', $config->baseUrl);
        $this->assertSame('my-token', $config->token);
        $this->assertSame(60, $config->timeout);
        $this->assertSame(15, $config->connectTimeout);
        $this->assertFalse($config->verifySsl);
    }

    public function test_construct_with_defaults(): void
    {
        $config = new SgmefConnectionConfig('https://api.example.com', 'token');

        $this->assertSame(30, $config->timeout);
        $this->assertSame(10, $config->connectTimeout);
        $this->assertTrue($config->verifySsl);
    }

    public function test_from_config_array(): void
    {
        $config = SgmefConnectionConfig::fromConfig([
            'api_url' => 'https://from-config.example.com',
            'token' => 'config-token',
            'http_options' => [
                'timeout' => 45,
                'connect_timeout' => 20,
                'verify' => false,
            ],
        ]);

        $this->assertSame('https://from-config.example.com', $config->baseUrl);
        $this->assertSame('config-token', $config->token);
        $this->assertSame(45, $config->timeout);
        $this->assertSame(20, $config->connectTimeout);
        $this->assertFalse($config->verifySsl);
    }

    public function test_from_values_with_overrides(): void
    {
        config()->set('lara_sgmef_qr.token', 'default-token');
        config()->set('lara_sgmef_qr.http_options.timeout', 30);

        $config = SgmefConnectionConfig::fromValues(
            'https://override.example.com',
            null, // utilise le token par défaut
            120   // override timeout
        );

        $this->assertSame('https://override.example.com', $config->baseUrl);
        $this->assertSame('default-token', $config->token);
        $this->assertSame(120, $config->timeout);
    }

    public function test_http_options_array(): void
    {
        $config = new SgmefConnectionConfig('https://api.example.com', 'token', 60, 15, false);

        $options = $config->httpOptions();

        $this->assertSame([
            'timeout' => 60,
            'connect_timeout' => 15,
            'verify' => false,
        ], $options);
    }

    public function test_immutable_properties(): void
    {
        $config = new SgmefConnectionConfig('https://api.example.com', 'token');

        // Les propriétés readonly ne peuvent pas être modifiées
        // Ce test vérifie que la classe est bien immutable
        $reflection = new \ReflectionClass($config);
        $baseUrlProp = $reflection->getProperty('baseUrl');
        $this->assertTrue($baseUrlProp->isReadOnly());
    }
}
