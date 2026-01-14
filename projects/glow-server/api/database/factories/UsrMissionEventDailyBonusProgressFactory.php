<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionEventDailyBonusProgress>
 */
class UsrMissionEventDailyBonusProgressFactory extends Factory
{
    protected $model = UsrMissionEventDailyBonusProgress::class;

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
            'mst_mission_event_daily_bonus_schedule_id' => '1',
            'progress' => 0,
            'latest_update_at' => null,
        ];
    }
}
