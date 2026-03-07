<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward>
 */
class MstUnitEncyclopediaRewardFactory extends Factory
{

    protected $model = MstUnitEncyclopediaReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'unit_encyclopedia_rank' => 1,
            'resource_type' => 'item',
            'resource_id' => fake()->uuid(),
            'resource_amount' => fake()->numberBetween(1, 100),
            'release_key' => 1,
        ];
    }
}
