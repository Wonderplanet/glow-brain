<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory>
 */
class LogCurrencyRevertHistoryFactory extends Factory
{
    protected $model = LogCurrencyRevertHistory::class;

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
            'comment' => '',
            'log_trigger_type' => '',
            'log_trigger_id' => '',
            'log_trigger_name' => '',
            'log_trigger_detail' => '',
            'log_request_id_type' => '',
            'log_request_id' => '',
            'log_created_at' => $this->faker->dateTime,
            'log_change_paid_amount' => 0,
            'log_change_free_amount' => 0,
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
