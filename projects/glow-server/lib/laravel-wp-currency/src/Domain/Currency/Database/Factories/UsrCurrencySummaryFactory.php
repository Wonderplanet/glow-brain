<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\UsrCurrencySummary>
 */
class UsrCurrencySummaryFactory extends Factory
{
    protected $model = UsrCurrencySummary::class;

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
            'paid_amount_apple' => 0,
            'paid_amount_google' => 0,
            'paid_amount_share' => 0,
            'free_amount' => 0,
        ];
    }
}
