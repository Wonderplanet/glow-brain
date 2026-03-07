<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionLimitedTermProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsrMissionLimitedTermProgress>
 */
class UsrMissionLimitedTermProgressFactory extends Factory
{
    protected $model = UsrMissionLimitedTermProgress::class;

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
