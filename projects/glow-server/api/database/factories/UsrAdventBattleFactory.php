<?php

namespace Database\Factories;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\AdventBattle\Eloquent\Models\UsrAdventBattle>
 */
class UsrAdventBattleFactory extends Factory
{
    protected $model = UsrAdventBattle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_advent_battle_id' => 'advent_battle_id_1',
            'max_score' => 0,
            'total_score' => 0,
            'challenge_count' => 0,
            'reset_challenge_count' => 0,
            'reset_ad_challenge_count' => 0,
            'clear_count' => 0,
            'max_received_max_score_reward' => 0,
            'is_ranking_reward_received' => false,
            'is_excluded_ranking' => false,
            'latest_reset_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
