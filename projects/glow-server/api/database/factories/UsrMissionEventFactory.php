<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<UsrMissionEvent>
 */
class UsrMissionEventFactory extends Factory
{
    protected $model = UsrMissionEvent::class;

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
            'mission_type' => MissionType::EVENT->getIntValue(),
            'is_open' => MissionUnlockStatus::OPEN->value,
            'latest_reset_at' => fake()->dateTime(),
        ];
    }
}
