<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionEventDaily>
 */
class MstMissionEventDailyFactory extends Factory
{

    protected $model = MstMissionEventDaily::class;

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
            'mst_event_id' => 'mst_event_id',
            'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
            'criterion_value' => 'stage1',
            'criterion_count' => 1,
            'group_key' => null,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'destination_scene' => 'scene1',
            'sort_order' => 0,
        ];
    }
}
