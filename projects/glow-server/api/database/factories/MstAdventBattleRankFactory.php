<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleRankType;
use App\Domain\Resource\Mst\Models\MstAdventBattleRank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstAdventBattleRank>
 */
class MstAdventBattleRankFactory extends Factory
{
    protected $model = MstAdventBattleRank::class;

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
            'rank_type' => AdventBattleRankType::BRONZE->value,
            'rank_level' => 1,
            'required_lower_score' => 10000000,
            'asset_key' => fake()->uuid(),
            'release_key' => 1,
        ];
    }
}
