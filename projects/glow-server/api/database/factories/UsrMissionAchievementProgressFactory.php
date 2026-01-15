<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionAchievementProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionAchievementProgress>
 */
class UsrMissionAchievementProgressFactory extends Factory
{
    protected $model = UsrMissionAchievementProgress::class;

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
            'criterion_key' => 'none',
            'progress' => 0,
        ];
    }
}
