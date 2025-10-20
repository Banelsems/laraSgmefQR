<?php

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Orchestra\Testbench\TestCase;

class AuthIndependenceTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [LaraSgmefQRServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('lara_sgmef_qr.default_operator', ['name' => 'Test Operator', 'id' => '1']);
        $app['config']->set('lara_sgmef_qr.web_interface', ['enabled' => true, 'middleware' => ['web'], 'route_prefix' => 'sgmef']);
    }

    /** @test */
    public function invoice_creation_works_without_auth_and_uses_default_operator()
    {
        $invoiceData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => ['name' => 'Test Client'],
            'items' => [['name' => 'Test Item', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']],
            'payment' => [['name' => 'ESPECES', 'amount' => 1000]],
        ];

        $dto = InvoiceRequestDto::from($invoiceData);

        $this->assertInstanceOf(OperatorDto::class, $dto->operator);
        $this->assertEquals('Test Operator', $dto->operator->name);
        $this->assertEquals('1', $dto->operator->id);
    }

    /** @test */
    public function dto_validation_works_with_default_operator()
    {
        $this->expectNotToPerformAssertions(); // S'assure qu'aucune exception n'est levée

        $invoiceData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => ['name' => 'Test Client'],
            'items' => [['name' => 'Test Item', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']],
            'payment' => [['name' => 'ESPECES', 'amount' => 1000]],
            'operator' => ['id' => config('lara_sgmef_qr.default_operator.id'), 'name' => config('lara_sgmef_qr.default_operator.name')],
        ];

        InvoiceRequestDto::from($invoiceData);
    }
}

