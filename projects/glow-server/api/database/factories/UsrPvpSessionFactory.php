<?php

namespace Database\Factories;

use App\Domain\Pvp\Models\UsrPvpSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Pvp\Models\UsrPvpSession>
 */
class UsrPvpSessionFactory extends Factory
{
    protected $model = UsrPvpSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'usr_user_id' => fake()->uuid(),
            'id' => fake()->uuid(),
            'sys_pvp_season_id' => 'default_pvp',
            'is_use_item' => 0,
            'party_no' => fake()->numberBetween(0, 10),
            'opponent_my_id' => fake()->optional()->uuid(),
            'opponent_pvp_status' => json_encode([]),
            'opponent_score' => fake()->numberBetween(0, 10000),
            'is_valid' => 0,
            'battle_start_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
