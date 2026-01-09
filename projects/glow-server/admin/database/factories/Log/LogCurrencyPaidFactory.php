<?php

declare(strict_types=1);

namespace Database\Factories\Log;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LogCurrencyPaidFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seq_no' => 1,
            'usr_user_id' => '1',
            'currency_paid_id' => fake()->uuid(),
            'receipt_unique_id' => fake()->uuid(),
            'is_sandbox' => 1,
            'query' => 'insert',
            'purchase_price' => '100',
            'purchase_amount' => 100,
            'price_per_amount' => '1',
            'vip_point' => 100,
            'currency_code' => 'JPY',
            'before_amount' => 0,
            'change_amount' => 100,
            'current_amount' => 100,
            'os_platform' => 'iOS',
            'billing_platform' => 'AppStore',
            'trigger_type' => 'dummy',
            'trigger_id' => '',
            'trigger_name' => 'dummy log',
            'trigger_detail' => 'dummy log detail',
            'request_id' => fake()->uuid(),
        ];
    }
}
