<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\LogCurrencyFree>
 */
class LogCurrencyFreeFactory extends Factory
{
    protected $model = LogCurrencyFree::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'logging_no' => 1,
            'usr_user_id' => $this->faker->unique()->uuid,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'before_ingame_amount' => 0,
            'before_bonus_amount' => 0,
            'before_reward_amount' => 0,
            'change_ingame_amount' => 0,
            'change_bonus_amount' => 0,
            'change_reward_amount' => 0,
            'current_ingame_amount' => 0,
            'current_bonus_amount' => 0,
            'current_reward_amount' => 0,
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
