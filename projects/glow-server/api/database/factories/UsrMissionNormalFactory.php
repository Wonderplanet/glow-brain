<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<UsrMissionNormal>
 */
class UsrMissionNormalFactory extends Factory
{
    protected $model = UsrMissionNormal::class;

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
            'mission_type' => 1,
            'mst_mission_id' => fake()->uuid(),
            'status' => 1,
            'is_open' => MissionUnlockStatus::OPEN->value,
            'progress' => 0,
            'unlock_progress' => 0,
            'latest_reset_at' => fake()->dateTime(),
        ];
    }
}
