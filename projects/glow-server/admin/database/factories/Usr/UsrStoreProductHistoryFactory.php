<?php

namespace Database\Factories\Usr;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adm\AdmUser>
 */
class UsrStoreProductHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // 記載されていないパラメータはDBのデフォルト値を使用する
        return [
            'id' => fake()->uuid(),
            'receipt_unique_id' => fake()->uuid(),
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'usr_user_id' => fake()->uuid(),
            'device_id' => fake()->uuid(),
            'age' => 0,
            'product_sub_id' => 'product_sub_id1',
            'platform_product_id' => 'product_id1',
            'mst_store_product_id' => 'mst_store_product_id1',
            'currency_code' => 'JPY',
            'receipt_bundle_id' => 'receipt_bundle_id1',
            'paid_amount' => 100,
            'free_amount' => 0,
            'purchase_price' => 100,
            'price_per_amount' => 100,
            'vip_point' => 100,
            'is_sandbox' => true,
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
            'deleted_at' => null,
        ];
    }
}
