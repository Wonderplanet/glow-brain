<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionAchievement>
 */
class MstMissionAchievementFactory extends Factory
{

    protected $model = MstMissionAchievement::class;

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
            'unlock_criterion_type' => null,
            'unlock_criterion_value' => null,
            'unlock_criterion_count' => 0,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'destination_scene' => 'scene1',
            'sort_order' => 0,
        ];
    }
}
