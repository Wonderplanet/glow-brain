<?php

namespace Database\Factories;

use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule>
 */
class MstMissionEventDailyBonusScheduleFactory extends Factory
{

    protected $model = MstMissionEventDailyBonusSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_event_id' => '1',
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2031-01-01 00:00:00',
            'release_key' => 1,
        ];
    }
}
