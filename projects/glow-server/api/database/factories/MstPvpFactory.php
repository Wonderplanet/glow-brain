<?php

namespace Database\Factories;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Mst\Models\MstPvp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvp>
 */
class MstPvpFactory extends Factory
{

    protected $model = MstPvp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => 'default_pvp',
            'max_daily_challenge_count' => 5,
            'max_daily_item_challenge_count' => 3,
            'item_challenge_cost_amount' => 1,
            'release_key' => 1,
            'ranking_min_pvp_rank_class' => PvpRankClassType::BRONZE->value,
            'initial_battle_point' => 0,
            'mst_in_game_id' => fake()->uuid(),
            'initial_battle_point' => 1000,
        ];
    }
}
