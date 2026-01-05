<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyFree;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\UsrCurrencyFree>
 */
class UsrCurrencyFreeFactory extends Factory
{
    protected $model = UsrCurrencyFree::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'usr_user_id' => $this->faker->unique()->uuid,
            'ingame_amount' => 0,
            'bonus_amount' => 0,
            'reward_amount' => 0,
        ];
    }
}
