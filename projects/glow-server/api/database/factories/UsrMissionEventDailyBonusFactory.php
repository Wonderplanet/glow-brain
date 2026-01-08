<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionEventDailyBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionEventDailyBonus>
 */
class UsrMissionEventDailyBonusFactory extends Factory
{
    protected $model = UsrMissionEventDailyBonus::class;

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
        ];
    }
}
