<?php

namespace Database\Factories;

use App\Domain\Stage\Models\UsrStageEnhance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Stage\Eloquent\Models\UsrStageEnhance>
 */
class UsrStageEnhanceFactory extends Factory
{
    protected $model = UsrStageEnhance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'usr_user_id' => fake()->uuid(),
            'mst_stage_id' => fake()->uuid(),
            'clear_count' => 0,
            'reset_challenge_count' => 0,
            'reset_ad_challenge_count' => 0,
            'max_score' => 0,
            'latest_reset_at' => fake()->dateTime(),
        ];
    }
}
