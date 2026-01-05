<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstExchangeLineup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstExchangeLineup>
 */
class MstExchangeLineupFactory extends Factory
{
    protected $model = MstExchangeLineup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'group_id' => fake()->uuid(),
            'tradable_count' => 10,
            'display_order' => fake()->numberBetween(0, 100),
        ];
    }
}
