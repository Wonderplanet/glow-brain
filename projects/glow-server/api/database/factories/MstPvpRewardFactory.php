<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvpReward>
 */
class MstPvpRewardFactory extends Factory
{

    protected $model = MstPvpReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'release_key' => 1,
            'mst_pvp_reward_group_id' => $this->faker->uuid(),
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
        ];
    }
}
