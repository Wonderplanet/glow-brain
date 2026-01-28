<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Tutorial\Enums\TutorialType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstTutorial>
 */
class MstTutorialFactory extends Factory
{
    protected $model = MstTutorial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'type' => TutorialType::MAIN,
            'sort_order' => 0,
            'function_name' => fake()->word(),
            'condition_type' => fake()->word(),
            'condition_value' => fake()->word(),
            'start_at' => fake()->dateTime(),
            'end_at' => fake()->dateTime(),
        ];
    }
}
