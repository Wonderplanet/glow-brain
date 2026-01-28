<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstDailyBonusReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDailyBonusReward>
 */
class MstDailyBonusRewardFactory extends Factory
{
    protected $model = MstDailyBonusReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'group_id' => fake()->uuid(),
            'resource_type' => 'item',
            'resource_id' => fake()->uuid(),
            'resource_amount' => fake()->numberBetween(1, 100),
            'release_key' => 1,
        ];
    }
}
