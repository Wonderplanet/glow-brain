<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstItemRarityTrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstItemRarityTrade>
 */
class MstItemRarityTradeFactory extends Factory
{

    protected $model = MstItemRarityTrade::class;

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
        ];
    }
}
