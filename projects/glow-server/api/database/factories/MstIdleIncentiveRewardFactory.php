<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstIdleIncentiveReward>
 */
class MstIdleIncentiveRewardFactory extends Factory
{

    protected $model = MstIdleIncentiveReward::class;

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
            'mst_stage_id' => fake()->uuid(),
            'base_coin_amount' => '1.23',
            'base_exp_amount' => '2.34',
            'mst_idle_incentive_item_group_id' => fake()->uuid(),
        ];
    }
}
