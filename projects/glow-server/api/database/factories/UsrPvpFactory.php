<?php

namespace Database\Factories;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\UsrPvp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Pvp\Models\UsrPvp>
 */
class UsrPvpFactory extends Factory
{
    protected $model = UsrPvp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'usr_user_id' => fake()->uuid(),
            'sys_pvp_season_id' => 'default_pvp',
            'score' => 1000,
            'max_received_score_reward' => 0,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'ranking' => 1000,
            'daily_remaining_challenge_count' => 5,
            'daily_remaining_item_challenge_count' => 3,
            'last_played_at' => now()->toDateTimeString(),
            'latest_reset_at' => now()->toDateTimeString(),
            'selected_opponent_candidates' => [],
            'is_excluded_ranking' => false,
            'is_season_reward_received' => false,
        ];
    }
}
