<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetFreeLog;

/**
 * @extends Factory<AdmBulkCurrencyRevertTaskTargetFreeLog>
 */
class AdmBulkCurrencyRevertTaskTargetFreeLogFactory extends Factory
{
    protected $model = AdmBulkCurrencyRevertTaskTargetFreeLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'adm_bulk_currency_revert_task_target_id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'log_currency_free_id' => fake()->uuid(),
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
