<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrUserBuyCount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Eloquent\Models\UsrUserBuyCount>
 */
class UsrUserBuyCountFactory extends Factory
{
    protected $model = UsrUserBuyCount::class;

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
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => fake()->dateTime(),
        ];
    }
}
