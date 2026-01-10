<?php

namespace Database\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattleClearReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstAdventBattleClearRewardFactory extends Factory
{
    protected $model = MstAdventBattleClearReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_advent_battle_id' => fake()->uuid(),
            'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => '1',
            'resource_amount' => '10',
            'percentage' => '10',
            'sort_order' => '1',
            'release_key' => 1,
        ];
    }
}
