<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\LogStore;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Billing\Models\LogStore>
 */
class LogStoreFactory extends Factory
{
    protected $model = LogStore::class;

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
            'platform_product_id' => '',
            'mst_store_product_id' => '',
            'product_sub_id' => '',
            'product_sub_name' => '',
            'raw_receipt' => '',
            'raw_price_string' => '',
            'currency_code' => 'JPY',
            'receipt_unique_id' => $this->faker->unique()->uuid,
            'receipt_bundle_id' => $this->faker->unique()->uuid,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'device_id' => $this->faker->unique()->uuid,
            'age' => 0,
            'paid_amount' => 0,
            'free_amount' => 0,
            'purchase_price' => '0.00',
            'price_per_amount' => '0.00',
            'vip_point' => 0,
            'is_sandbox' => true,
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
