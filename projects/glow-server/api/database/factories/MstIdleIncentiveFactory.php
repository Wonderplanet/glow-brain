<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstIdleIncentive>
 */
class MstIdleIncentiveFactory extends Factory
{

    protected $model = MstIdleIncentive::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'asset_key' => fake()->uuid(),
            'initial_reward_receive_minutes' => fake()->numberBetween(1, 1000),
            'reward_increase_interval_minutes' => fake()->numberBetween(1, 1000),
            'max_idle_hours' => fake()->numberBetween(1, 1000),
            'max_daily_diamond_quick_receive_amount' => fake()->numberBetween(1, 1000),
            'required_quick_receive_diamond_amount' => fake()->numberBetween(1, 1000),
            'max_daily_ad_quick_receive_amount' => fake()->numberBetween(1, 1000),
            'ad_interval_seconds' => fake()->numberBetween(1, 1000),
            'quick_idle_minutes' => fake()->numberBetween(1, 1000),
        ];
    }
}
