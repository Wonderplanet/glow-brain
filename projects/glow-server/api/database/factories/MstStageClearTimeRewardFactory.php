<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstStageClearTimeReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstStageClearTimeRewardFactory extends Factory
{
    protected $model = MstStageClearTimeReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_stage_id' => fake()->uuid(),
            'upper_clear_time_ms' => 10000,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'release_key' => 1,
        ];
    }
}
