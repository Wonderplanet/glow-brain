<?php

namespace Database\Factories;

use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\IdleIncentive\Eloquent\Models\UsrIdleIncentive>
 */
class UsrIdleIncentiveFactory extends Factory
{
    protected $model = UsrIdleIncentive::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'diamond_quick_receive_count' => fake()->numberBetween(0, 100),
            'ad_quick_receive_count' => fake()->numberBetween(0, 100),
            'idle_started_at' => fake()->dateTime(),
            'diamond_quick_receive_at' => fake()->dateTime(),
            'ad_quick_receive_at' => fake()->dateTime(),
            'reward_mst_stage_id' => null,
        ];
    }
}
