<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprStepupGachaStepReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprStepupGachaStepReward>
 */
class OprStepupGachaStepRewardFactory extends Factory
{
    protected $model = OprStepupGachaStepReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'release_key' => 1,
            'opr_gacha_id' => fake()->uuid(),
            'step_number' => fake()->numberBetween(1, 10),
            'loop_count_target' => null,
            'resource_type' => fake()->randomElement(['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Unit']),
            'resource_id' => fake()->optional(0.7)->uuid(),
            'resource_amount' => fake()->numberBetween(1, 1000),
        ];
    }
}
