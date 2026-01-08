<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;

/**
 * @extends Factory<AdmBulkCurrencyRevertTask>
 */
class AdmBulkCurrencyRevertTaskFactory extends Factory
{
    protected $model = AdmBulkCurrencyRevertTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'adm_user_id' => fake()->randomNumber(),
            'file_name' => 'test',
            'revert_currency_num' => 0,
            'comment' => 'test',
            'status' => AdmBulkCurrencyRevertTaskStatus::Ready,
            'total_count' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'error_message' => null,
        ];
    }
}
