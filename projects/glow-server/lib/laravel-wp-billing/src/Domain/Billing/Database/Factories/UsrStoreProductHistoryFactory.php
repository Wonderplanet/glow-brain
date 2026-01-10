<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory>
 */
class UsrStoreProductHistoryFactory extends Factory
{
    protected $model = UsrStoreProductHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'receipt_unique_id' => $this->faker->unique()->uuid,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'usr_user_id' => $this->faker->unique()->uuid,
            'device_id' => $this->faker->unique()->uuid,
            'age' => 0,
            'product_sub_id' => '',
            'platform_product_id' => '',
            'mst_store_product_id' => '',
            'currency_code' => 'JPY',
            'receipt_bundle_id' => $this->faker->unique()->uuid,
            'receipt_purchase_token' => '',
            'paid_amount' => 0,
            'free_amount' => 0,
            'purchase_price' => '0.00',
            'price_per_amount' => '0.00',
            'vip_point' => 0,
            'is_sandbox' => true,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
        ];
    }
}
