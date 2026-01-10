<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule>
 */
class MngJumpPlusRewardScheduleFactory extends Factory
{

    protected $model = MngJumpPlusRewardSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'group_id' => fake()->uuid(),
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
