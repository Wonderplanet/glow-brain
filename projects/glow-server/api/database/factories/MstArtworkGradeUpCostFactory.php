<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUpCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstArtworkGradeUpCost>
 */
class MstArtworkGradeUpCostFactory extends Factory
{
    protected $model = MstArtworkGradeUpCost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_artwork_grade_up_id' => fake()->uuid(),
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => fake()->uuid(),
            'resource_amount' => 10,
        ];
    }
}
