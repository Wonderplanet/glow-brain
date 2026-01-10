<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstExchangeCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstExchangeCost>
 */
class MstExchangeCostFactory extends Factory
{
    protected $model = MstExchangeCost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_exchange_lineup_id' => fake()->uuid(),
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
            'release_key' => 1,
        ];
    }
}
