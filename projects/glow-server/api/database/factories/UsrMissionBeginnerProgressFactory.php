<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionBeginnerProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionBeginnerProgress>
 */
class UsrMissionBeginnerProgressFactory extends Factory
{
    protected $model = UsrMissionBeginnerProgress::class;

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
