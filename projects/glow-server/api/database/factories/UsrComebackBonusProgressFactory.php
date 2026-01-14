<?php

namespace Database\Factories;

use App\Domain\DailyBonus\Models\UsrComebackBonusProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\DailyBonus\Models\UsrComebackBonusProgress>
 */
class UsrComebackBonusProgressFactory extends Factory
{
    protected $model = UsrComebackBonusProgress::class;

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
            'mst_comeback_bonus_schedule_id' => '1',
            'start_count' => 1,
            'progress' => 0,
            'latest_update_at' => null,
        ];
    }
}
