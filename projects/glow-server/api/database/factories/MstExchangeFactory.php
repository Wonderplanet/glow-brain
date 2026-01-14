<?php

namespace Database\Factories;

use App\Domain\Exchange\Enums\ExchangeTradeType;
use App\Domain\Resource\Mst\Models\MstExchange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstExchange>
 */
class MstExchangeFactory extends Factory
{
    protected $model = MstExchange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'exchange_trade_type' => ExchangeTradeType::NORMAL_EXCHANGE_TRADE->value,
            'start_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            'end_at' => fake()->dateTime('+1 year')->format('Y-m-d H:i:s'),
            'lineup_group_id' => fake()->uuid(),
            'display_order' => fake()->numberBetween(0, 100),
        ];
    }
}
