<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngMessageReward>
 */
class MngMessageRewardFactory extends Factory
{

    protected $model = MngMessageReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mng_message_id' => fake()->uuid(),
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => null,
            'display_order' => 1,
        ];
    }
}
