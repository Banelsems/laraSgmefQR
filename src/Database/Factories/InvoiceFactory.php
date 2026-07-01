<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Database\Factories;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(1000, 250000);

        return [
            'uid' => 'uid-' . $this->faker->unique()->bothify('########'),
            'ifu' => '1234567890123',
            'customer_ifu' => null,
            'type' => 'FV',
            'status' => InvoiceStatusEnum::PENDING,
            'reference' => null,
            'total_amount' => $total,
            'raw_request' => [
                'client' => ['name' => $this->faker->name()],
                'operator' => ['id' => '1', 'name' => 'Operator Test'],
                'items' => [['name' => 'Article Test', 'price' => $total, 'quantity' => 1, 'taxGroup' => 'B']],
            ],
            'raw_response' => ['uid' => null, 'total' => $total],
            'security_elements' => null,
            'qr_code_data' => null,
            'mecf_code' => null,
            'error_code' => null,
            'error_description' => null,
            'expires_at' => now()->addMinutes(2),
            'confirmed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
