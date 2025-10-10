<?php

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
use Banelsems\LaraSgmefQr\DTOs\ClientDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceItemDto;
use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\DTOs\OperatorDto;
use Banelsems\LaraSgmefQr\DTOs\PaymentDto;
use Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

/**
 * Tests pour vérifier l'indépendance totale du package vis-à-vis de l'authentification
 */
class AuthIndependenceTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LaraSgmefQRServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('lara_sgmef_qr.default_operator', [
            'name' => 'Test Operator',
            'id' => '1',
        ]);
        
        $app['config']->set('lara_sgmef_qr.web_interface', [
            'enabled' => true,
            'middleware' => ['web'], // Pas d'auth
            'route_prefix' => 'sgmef',
        ]);
    }

    /** @test */
    public function package_works_without_authentication_system()
    {
        // Arrange - Aucun utilisateur connecté, aucun système d'auth
        $this->assertGuest();

        // Act & Assert - Le package doit fonctionner
        $invoiceManager = app(InvoiceManagerInterface::class);
        $this->assertInstanceOf(InvoiceManagerInterface::class, $invoiceManager);
    }

    /** @test */
    public function default_operator_is_available_without_auth()
    {
        // Arrange - Aucun utilisateur connecté
        $this->assertGuest();

        // Act
        $defaultOperator = app('sgmef.default_operator');

        // Assert
        $this->assertIsArray($defaultOperator);
        $this->assertArrayHasKey('name', $defaultOperator);
        $this->assertArrayHasKey('id', $defaultOperator);
        $this->assertEquals('Test Operator', $defaultOperator['name']);
        $this->assertEquals('1', $defaultOperator['id']);
    }

    /** @test */
    public function web_routes_are_accessible_without_auth()
    {
        // Act & Assert - Les routes doivent être accessibles sans authentification
        $response = $this->get('/sgmef');
        $response->assertStatus(200);

        $response = $this->get('/sgmef/invoices');
        $response->assertStatus(200);

        $response = $this->get('/sgmef/config');
        $response->assertStatus(200);
    }

    /** @test */
    public function invoice_creation_works_without_auth()
    {
        // Arrange - Données de facture sans opérateur spécifié
        $invoiceData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => [
                'name' => 'Test Client',
            ],
            'items' => [
                [
                    'name' => 'Test Item',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B',
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000,
                ]
            ],
            // Pas d'opérateur spécifié - doit utiliser celui par défaut
        ];

        // Act - Création du DTO sans authentification
        $dto = InvoiceRequestDto::fromArray($invoiceData);

        // Assert - L'opérateur par défaut doit être utilisé
        $this->assertInstanceOf(OperatorDto::class, $dto->operator);
        $this->assertEquals('Test Operator', $dto->operator->name);
        $this->assertEquals('1', $dto->operator->id);
    }

    /** @test */
    public function invoice_form_submission_works_without_auth()
    {
        // Arrange - Données de formulaire
        $formData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => [
                'name' => 'Test Client',
            ],
            'items' => [
                [
                    'name' => 'Test Item',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B',
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000,
                ]
            ],
            // Opérateur vide - doit être rempli automatiquement
            'operator' => [
                'name' => '',
                'id' => '',
            ],
        ];

        // Act - Soumission du formulaire sans authentification
        $response = $this->post('/sgmef/invoices', $formData);

        // Assert - Doit fonctionner (redirection ou succès)
        $this->assertTrue(
            $response->isRedirection() || $response->isSuccessful(),
            'La soumission de facture doit fonctionner sans authentification'
        );
    }

    /** @test */
    public function preview_endpoint_works_without_auth()
    {
        // Arrange
        $previewData = [
            'ifu' => '1234567890123',
            'type' => 'FV',
            'client' => [
                'name' => 'Test Client',
            ],
            'items' => [
                [
                    'name' => 'Test Item',
                    'price' => 1000,
                    'quantity' => 1,
                    'taxGroup' => 'B',
                ]
            ],
            'payment' => [
                [
                    'name' => 'ESPECES',
                    'amount' => 1000,
                ]
            ],
        ];

        // Act
        $response = $this->postJson('/sgmef/invoices/preview', $previewData);

        // Assert - Doit fonctionner même sans authentification
        $this->assertTrue(
            $response->isSuccessful() || $response->status() === 422,
            'Le preview doit fonctionner sans authentification'
        );
    }

    /** @test */
    public function static_helper_method_works()
    {
        // Act
        $defaultOperator = LaraSgmefQRServiceProvider::getDefaultOperator();

        // Assert
        $this->assertIsArray($defaultOperator);
        $this->assertArrayHasKey('name', $defaultOperator);
        $this->assertArrayHasKey('id', $defaultOperator);
    }

    /** @test */
    public function package_services_are_resolvable_without_auth()
    {
        // Act & Assert - Tous les services doivent être résolvables
        $apiClient = app('sgmef.api');
        $this->assertNotNull($apiClient);

        $invoiceManager = app('sgmef.invoices');
        $this->assertNotNull($invoiceManager);

        $defaultOperator = app('sgmef.default_operator');
        $this->assertNotNull($defaultOperator);
    }

    /** @test */
    public function dto_validation_works_with_default_operator()
    {
        // Arrange - DTO avec opérateur par défaut
        $dto = new InvoiceRequestDto(
            ifu: '1234567890123',
            type: 'FV',
            items: [
                new InvoiceItemDto(
                    name: 'Test Item',
                    price: 1000,
                    quantity: 1,
                    taxGroup: 'B'
                )
            ],
            client: new ClientDto(name: 'Test Client'),
            operator: new OperatorDto(
                id: config('lara_sgmef_qr.default_operator.id'),
                name: config('lara_sgmef_qr.default_operator.name')
            ),
            payment: [
                new PaymentDto(name: 'ESPECES', amount: 1000)
            ]
        );

        // Act
        $errors = $dto->validate();

        // Assert - Aucune erreur de validation
        $this->assertEmpty($errors, 'La validation doit passer avec l\'opérateur par défaut');
    }
}
