<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionEventDaily;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionEventDaily>
 */
class UsrMissionEventDailyFactory extends Factory
{
    protected $model = UsrMissionEventDaily::class;

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
