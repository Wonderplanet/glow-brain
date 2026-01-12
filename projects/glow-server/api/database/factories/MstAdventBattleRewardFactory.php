<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstAdventBattleReward>
 */
class MstAdventBattleRewardFactory extends Factory
{
    protected $model = MstAdventBattleReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_advent_battle_reward_group_id' => 'group_id_1',
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => fake()->uuid(),
            'resource_amount' => fake()->numberBetween(1, 100),
            'release_key' => 1,
        ];
    }
}
