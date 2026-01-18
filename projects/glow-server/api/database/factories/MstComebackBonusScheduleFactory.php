<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstComebackBonusSchedule>
 */
class MstComebackBonusScheduleFactory extends Factory
{
    protected $model = MstComebackBonusSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'inactive_condition_days' => 1,
            'duration_days' => 5,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2031-01-01 00:00:00',
            'release_key' => 1,
        ];
    }
}
