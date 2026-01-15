<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\LogCurrencyPaid>
 */
class LogCurrencyPaidFactory extends Factory
{
    protected $model = LogCurrencyPaid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'seq_no' => 0,
            'usr_user_id' => $this->faker->unique()->uuid,
            'currency_paid_id' => $this->faker->unique()->uuid,
            'receipt_unique_id' => $this->faker->unique()->uuid,
            'is_sandbox' => true,
            'query' => '',
            'purchase_price' => '0.00',
            'purchase_amount' => 0,
            'price_per_amount' => '0.00',
            'vip_point' => 0,
            'currency_code' => 'JPY',
            'before_amount' => 0,
            'change_amount' => 0,
            'current_amount' => 0,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'trigger_type' => '',
            'trigger_id' => '',
            'trigger_name' => '',
            'trigger_detail' => '',
            'request_id_type' => '',
            'request_id' => '',
            'nginx_request_id' => '',
        ];
    }
}
