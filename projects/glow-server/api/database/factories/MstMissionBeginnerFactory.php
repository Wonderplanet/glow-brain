<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionBeginner>
 */
class MstMissionBeginnerFactory extends Factory
{

    protected $model = MstMissionBeginner::class;

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
            'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
            'criterion_value' => 'stage1',
            'criterion_count' => 1,
            'unlock_day' => 0,
            'group_key' => 'key1',
            'bonus_point' => 0,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'sort_order' => 0,
            'destination_scene' => 'scene1',
        ];
    }
}
