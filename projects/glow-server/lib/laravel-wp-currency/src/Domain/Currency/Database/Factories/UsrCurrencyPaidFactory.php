<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid>
 */
class UsrCurrencyPaidFactory extends Factory
{
    protected $model = UsrCurrencyPaid::class;

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
            'left_amount' => 0,
            'purchase_price' => '0.00',
            'purchase_amount' => 0,
            'price_per_amount' => '0.00',
            'vip_point' => 0,
            'currency_code' => 'JPY',
            'receipt_unique_id' => $this->faker->unique()->uuid,
            'is_sandbox' => true,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
        ];
    }
}
