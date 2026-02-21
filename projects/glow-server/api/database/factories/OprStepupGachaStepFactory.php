<?php

namespace Database\Factories;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprStepupGachaStep>
 */
class OprStepupGachaStepFactory extends Factory
{
    protected $model = OprStepupGachaStep::class;

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
            'step_number' => fake()->numberBetween(1, 10),
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => fake()->uuid(),
            'cost_num' => fake()->numberBetween(100, 1000),
            'draw_count' => fake()->numberBetween(1, 10),
            'fixed_prize_count' => 0,
            'fixed_prize_rarity_threshold_type' => RarityType::N->value,
            'prize_group_id' => fake()->uuid(),
            'fixed_prize_group_id' => null,
            'is_first_free' => false,
        ];
    }
}

