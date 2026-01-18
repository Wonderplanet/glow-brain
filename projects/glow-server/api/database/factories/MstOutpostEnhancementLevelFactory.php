<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel>
 */
class MstOutpostEnhancementLevelFactory extends Factory
{

    protected $model = MstOutpostEnhancementLevel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_outpost_enhancement_id' => fake()->uuid(),
            'level' => fake()->numberBetween(1, 10),
            'cost_coin' => fake()->numberBetween(0, 1000),
            'enhancement_value' => fake()->randomFloat(2, 0, 1000),
            'release_key' => 1,
        ];
    }
}
