<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstQuestEventBonusSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstQuestEventBonusScheduleFactory extends Factory
{
    protected $model = MstQuestEventBonusSchedule::class;

    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'mst_quest_id' => fake()->uuid(),
            'event_bonus_group_id' => 'eventBonusGroupId1',
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2031-01-01 00:00:00',
        ];
    }
}
