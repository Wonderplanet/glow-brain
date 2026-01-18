<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprStepUpGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprStepUpGacha>
 */
class OprStepUpGachaFactory extends Factory
{
    protected $model = OprStepUpGacha::class;

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
            'opr_gacha_id' => fake()->uuid(),
            'max_step_number' => fake()->numberBetween(3, 10),
            'max_loop_count' => fake()->numberBetween(1, 5),
        ];
    }
}
