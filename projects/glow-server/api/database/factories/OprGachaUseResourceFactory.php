<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprGachaUseResource>
 */
class OprGachaUseResourceFactory extends Factory
{

    protected $model = OprGachaUseResource::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'opr_gacha_id' => fake()->uuid(),
            'cost_type' => 'Diamond',
            'cost_id' => fake()->uuid(),
            'cost_num' => 1,
            'draw_count' => 1,
            'cost_priority' => fake()->numberBetween(1, 100),
        ];
    }
}
