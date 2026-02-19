<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;

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
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_artwork_id' => fake()->uuid(),
            'receipt_unique_id' => fake()->uuid(),
            'os_platform' => 'iOS',
            'billing_platform' => 'AppStore',
            'device_id' => fake()->uuid(),
            'age' => 20,
            'product_sub_id' => fake()->uuid(),
            'platform_product_id' => fake()->uuid(),
            'mst_store_product_id' => fake()->uuid(),
            'currency_code' => 'JPY',
            'receipt_bundle_id' => 'com.example.app',
            'receipt_purchase_token' => fake()->uuid(),
            'paid_amount' => 0,
            'free_amount' => 0,
            'purchase_price' => 0.00,
            'price_per_amount' => 0.00,
            'vip_point' => 0,
            'is_sandbox' => 0,
        ];
    }
}
