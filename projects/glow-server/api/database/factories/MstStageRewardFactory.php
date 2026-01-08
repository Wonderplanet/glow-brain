<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Domain\Stage\Enums\StageRewardCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstStageRewardFactory extends Factory
{
    protected $model = MstStageReward::class;

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
            'reward_category' => StageRewardCategory::FIRST_CLEAR->value,
            'resource_type' => RewardType::STAMINA->value,
            'resource_id' => '1',
            'resource_amount' => '10',
            'percentage' => '10',
            'release_key' => fake()->numberBetween(1, 100),
            'sort_order' => '1',
        ];
    }
}
