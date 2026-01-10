<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrShopPass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Eloquent\Models\UsrShopPass>
 */
class UsrShopPassFactory extends Factory
{
    protected $model = UsrShopPass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_shop_pass_id' => fake()->uuid(),
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            'start_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            'end_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
