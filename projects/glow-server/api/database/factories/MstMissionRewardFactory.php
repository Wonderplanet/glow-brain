<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionReward>
 */
class MstMissionRewardFactory extends Factory
{

    protected $model = MstMissionReward::class;

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
            'group_id' => fake()->uuid(),
            'resource_type' => 'item',
            'resource_id' => fake()->uuid(),
            'resource_amount' => fake()->numberBetween(1, 100),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
