<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog>
 */
class LogCurrencyRevertHistoryPaidLogFactory extends Factory
{
    protected $model = LogCurrencyRevertHistoryPaidLog::class;

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
            'log_currency_revert_history_id' => $this->faker->unique()->uuid,
            'log_currency_paid_id' => $this->faker->unique()->uuid,
            'revert_log_currency_paid_id' => $this->faker->unique()->uuid,
        ];
    }
}
