<?php

namespace Database\Factories;

use App\Domain\Exchange\Models\LogExchangeAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Exchange\Models\LogExchangeAction>
 */
class LogExchangeActionFactory extends Factory
{
    protected $model = LogExchangeAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'nginx_request_id' => fake()->uuid(),
            'request_id' => fake()->uuid(),
            'logging_no' => 1,
            'mst_exchange_lineup_id' => fake()->uuid(),
            'costs' => json_encode([]),
            'rewards' => json_encode([]),
            'trade_count' => 1,
        ];
    }
}
