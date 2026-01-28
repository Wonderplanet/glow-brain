<?php

namespace Database\Factories;

use App\Domain\Exchange\Models\UsrExchangeLineup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Exchange\Models\UsrExchangeLineup>
 */
class UsrExchangeLineupFactory extends Factory
{
    protected $model = UsrExchangeLineup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usr_user_id' => fake()->uuid(),
            'mst_exchange_lineup_id' => fake()->uuid(),
            'mst_exchange_id' => fake()->uuid(),
            'trade_count' => 0,
            'reset_at' => now()->toDateTimeString(),
        ];
    }
}
