<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Billing\Models\LogAllowance;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Billing\Models\LogAllowance>
 */
class LogAllowanceFactory extends Factory
{
    protected $model = LogAllowance::class;

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
            'product_sub_id' => '',
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'product_id' => '',
            'mst_store_product_id' => '',
            'billing_platform' => CurrencyConstants::PLATFORM_APPSTORE,
            'device_id' => $this->faker->unique()->uuid,
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
