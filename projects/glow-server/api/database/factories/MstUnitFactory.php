<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnit>
 */
class MstUnitFactory extends Factory
{
    protected $model = MstUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'fragment_mst_item_id' => fake()->uuid(),
            'color' => 'Colorless',
            'role_type' => 'Attack',
            'attack_range_type' => 'Short',
            'unit_label' => 'DropR',
            'has_specific_rank_up' => 0,
            'mst_series_id' => fake()->uuid(),
            'asset_key' => '1',
            'rarity' => 'N',
            'sort_order' => 1,
            'summon_cost' => 1,
            'summon_cool_time' => 1,
            'special_attack_initial_cool_time' => 1,
            'special_attack_cool_time' => 1,
            'min_hp' => 1,
            'max_hp' => 1,
            'damage_knock_back_count' => 1,
            'move_speed' => '1.11',
            'well_distance' => 1,
            'min_attack_power' => 1,
            'max_attack_power' => 1,
            'mst_unit_ability_id1' => '1',
            'ability_unlock_rank1' => 1,
            'mst_unit_ability_id2' => '2',
            'ability_unlock_rank2' => 2,
            'mst_unit_ability_id3' => '3',
            'ability_unlock_rank3' => 3,
            'is_encyclopedia_special_attack_position_right' => 0,
            'release_key' => 1,
        ];
    }
}
