<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Billing\Models\UsrStoreAllowance>
 */
class UsrStoreAllowanceFactory extends Factory
{
    protected $model = UsrStoreAllowance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'usr_user_id' => $this->faker->unique()->uuid,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'product_id' => '',
            'mst_store_product_id' => '',
            'product_sub_id' => '',
            'device_id' => '',
        ];
    }
}
