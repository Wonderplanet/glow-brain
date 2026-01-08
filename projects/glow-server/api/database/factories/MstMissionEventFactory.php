<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \FactoryIlluminate\Database\Eloquent\Factories\<\App\Domain\Resource\Mst\Models\MstMissionEvent>
 */
class MstMissionEventFactory extends Factory
{

    protected $model = MstMissionEvent::class;

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
            'unlock_criterion_type' => null,
            'unlock_criterion_value' => null,
            'unlock_criterion_count' => 0,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'destination_scene' => 'scene1',
            'sort_order' => 0,
        ];
    }
}
