<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MstMissionLimitedTerm>
 */
class MstMissionLimitedTermFactory extends Factory
{

    protected $model = MstMissionLimitedTerm::class;

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
            'progress_group_key' => 'progress_group_key',
            'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT->value,
            'criterion_value' => null,
            'criterion_count' => 1,
            'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'sort_order' => 0,
            'destination_scene' => 'scene1',
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
