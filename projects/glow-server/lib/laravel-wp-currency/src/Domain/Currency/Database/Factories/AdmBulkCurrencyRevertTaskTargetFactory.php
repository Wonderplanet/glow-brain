<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;

/**
 * @extends Factory<AdmBulkCurrencyRevertTaskTarget>
 */
class AdmBulkCurrencyRevertTaskTargetFactory extends Factory
{
    protected $model = AdmBulkCurrencyRevertTaskTarget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'adm_bulk_currency_revert_task_id' => fake()->uuid(),
            'seq_no' => 1,
            'usr_user_id' => fake()->uuid(),
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Ready,
            'revert_currency_num' => 0,
            'consumed_at' => fake()->dateTime(),
            'trigger_type' => 'test',
            'trigger_id' => fake()->uuid(),
            'trigger_name' => 'test',
            'request_id' => fake()->uuid(),
            'sum_log_change_amount_paid' => 0,
            'sum_log_change_amount_free' => 0,
            'comment' => 'test',
            'error_message' => null,
        ];
    }
}
