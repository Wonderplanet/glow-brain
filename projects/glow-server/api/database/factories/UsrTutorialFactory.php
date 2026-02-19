<?php

namespace Database\Factories;

use App\Domain\Tutorial\Models\UsrTutorial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Tutorial\Models\UsrTutorial>
 */
class UsrTutorialFactory extends Factory
{
    protected $model = UsrTutorial::class;

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
            'mst_tutorial_id' => fake()->uuid(),
        ];
    }
}
