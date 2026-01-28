<?php

namespace Database\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\AdventBattle\Eloquent\Models\UsrAdventBattleSession>
 */
class UsrAdventBattleSessionFactory extends Factory
{
    protected $model = UsrAdventBattleSession::class;

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
            'is_valid' => AdventBattleSessionStatus::CLOSED,
            'party_no' => 0, // 0は無効なパーティ番号
            'battle_start_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
