<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionDailyBonus>
 */
class MstMissionDailyBonusFactory extends Factory
{

    protected $model = MstMissionDailyBonus::class;

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
            'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
            'login_day_count' => 1,
            'mst_mission_reward_group_id' => fake()->uuid(),
            'sort_order' => 0,
        ];
    }
}
