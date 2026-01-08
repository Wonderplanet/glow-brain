<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup>
 */
class MstAdventBattleRewardGroupFactory extends Factory
{
    protected $model = MstAdventBattleRewardGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_advent_battle_id' => 'advent_battle_id_1',
            'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
            'condition_value' => 10,
            'release_key' => 1,
        ];
    }
}
