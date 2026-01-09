<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionStatus>
 */
class UsrMissionStatusFactory extends Factory
{
    protected $model = UsrMissionStatus::class;

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
            'beginner_mission_status' => 0,
            'mission_unlocked_at' => fake()->dateTime(),
            'latest_mst_hash' => 'mstHash',
        ];
    }
}
