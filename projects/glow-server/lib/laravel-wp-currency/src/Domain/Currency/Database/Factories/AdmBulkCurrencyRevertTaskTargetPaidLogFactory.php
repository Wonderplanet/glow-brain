<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetPaidLog;

/**
 * @extends Factory<AdmBulkCurrencyRevertTaskTargetPaidLog>
 */
class AdmBulkCurrencyRevertTaskTargetPaidLogFactory extends Factory
{
    protected $model = AdmBulkCurrencyRevertTaskTargetPaidLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'adm_bulk_currency_revert_task_target_id' => AdmBulkCurrencyRevertTaskTarget::factory(),
            'usr_user_id' => fake()->uuid(),
            'log_currency_paid_id' => fake()->uuid(),
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
