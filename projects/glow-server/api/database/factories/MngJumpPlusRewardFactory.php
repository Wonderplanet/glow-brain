<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngJumpPlusReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngJumpPlusReward>
 */
class MngJumpPlusRewardFactory extends Factory
{

    protected $model = MngJumpPlusReward::class;

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
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 1,
        ];
    }
}
