<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstInGame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstInGame>
 */
class MstInGameFactory extends Factory
{
    protected $model = MstInGame::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'mst_auto_player_sequence_id' => '',
            'bgm_asset_key' => '',
            'boss_bgm_asset_key' => '',
            'loop_background_asset_key' => '',
            'player_outpost_asset_key' => 'default_outpost',
            'mst_page_id' => '',
            'mst_enemy_outpost_id' => '',
            'mst_defense_target_id' => '',
            'boss_mst_enemy_stage_parameter_id' => '',
            'boss_count' => 1,
            'normal_enemy_hp_coef' => 1.0,
            'normal_enemy_attack_coef' => 1.0,
            'normal_enemy_speed_coef' => 1.0,
            'boss_enemy_hp_coef' => 1.0,
            'boss_enemy_attack_coef' => 1.0,
            'boss_enemy_speed_coef' => 1.0,
        ];
    }
}
