<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstExchangeReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstExchangeReward>
 */
class MstExchangeRewardFactory extends Factory
{
    protected $model = MstExchangeReward::class;

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
            'resource_type' => 'Item',
            'resource_id' => fake()->uuid(),
            'resource_amount' => 1,
            'release_key' => 1,
        ];
    }
}
