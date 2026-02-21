<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstAdventBattle>
 */
class MstAdventBattleFactory extends Factory
{
    protected $model = MstAdventBattle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_event_id' => 'event1',
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'mst_stage_rule_group_id' => 'group_id_1',
            'event_bonus_group_id' => '',
            'challengeable_count' => 5,
            'ad_challengeable_count' => 10,
            'display_mst_unit_id1' => 'unit_id_1',
            'display_mst_unit_id2' => 'unit_id_2',
            'display_mst_unit_id3' => 'unit_id_3',
            'exp' => 5,
            'coin' => 10,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
            'release_key' => 1,
            'score_addition_type' => 'AllEnemiesAndOutPost',
            'score_additional_coef' => 1.0,
        ];
    }
}
