<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprGachaPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprGachaPrize>
 */
class OprGachaPrizeFactory extends Factory
{

    protected $model = OprGachaPrize::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'group_id' => fake()->uuid(),
            'resource_type' => 'Coin',
            'resource_id' => fake()->uuid(),
            'resource_amount' => 1,
            'weight' => fake()->numberBetween(1, 100),
            'pickup' => false,
        ];
    }
}
