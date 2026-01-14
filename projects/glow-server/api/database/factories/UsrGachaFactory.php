<?php

namespace Database\Factories;

use App\Domain\Gacha\Models\UsrGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Gacha\Eloquent\Models\UsrGacha>
 */
class UsrGachaFactory extends Factory
{
    protected $model = UsrGacha::class;

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
            'opr_gacha_id' => fake()->uuid(),
            'ad_played_at' => null,
            'played_at' => null,
            'ad_count' => 0,
            'ad_daily_count' => 0,
            'count' => 0,
            'daily_count' => 0,
            'expires_at' => null,
        ];
    }
}
