<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrTradePack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Models\UsrTradePack>
 */
class UsrTradePackFactory extends Factory
{
    protected $model = UsrTradePack::class;

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
            'mst_pack_id' => fake()->uuid(),
            'daily_trade_count' => 0,
            'last_reset_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
