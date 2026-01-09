<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstShopPassReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstShopPass>
 */
class MstShopPassRewardFactory extends Factory
{
    protected $model = MstShopPassReward::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_shop_pass_id' => fake()->uuid(),
            'pass_reward_type' => 1,
            'resource_type' => RewardType::COIN,
            'resource_id' => null,
            'resource_amount' => 100,
            'release_key' => 1,
        ];
    }
}
