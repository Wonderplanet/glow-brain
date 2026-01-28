<?php

namespace Database\Factories;

use App\Domain\Mission\Models\UsrMissionRecentAddition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Mission\Eloquent\Models\UsrMissionRecentAddition>
 */
class UsrMissionRecentAdditionFactory extends Factory
{
    protected $model = UsrMissionRecentAddition::class;

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
            'mission_type' => fake()->word(),
            'latest_release_key' => 1,
        ];
    }
}
