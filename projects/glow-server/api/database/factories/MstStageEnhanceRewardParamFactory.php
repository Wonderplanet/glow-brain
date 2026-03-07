<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstStageEnhanceRewardParam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstStageEnhanceRewardParam>
 */
class MstStageEnhanceRewardParamFactory extends Factory
{

    protected $model = MstStageEnhanceRewardParam::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'min_threshold_score' => 100,
            'coin_reward_amount' => 100,
            'coin_reward_size_type' => '',
            'release_key' => 1,
        ];
    }
}
