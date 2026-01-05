<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprCoinProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprCoinProduct>
 */
class OprCoinProductFactory extends Factory
{

    protected $model = OprCoinProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'asset_key' => 'asset_key',
            'coin_amount' => 100,
            'required_diamond_amount' => 10,
            'is_free' => 1,
        ];
    }
}
