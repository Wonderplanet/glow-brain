<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstBoxGachaGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstBoxGachaGroup>
 */
class MstBoxGachaGroupFactory extends Factory
{
    protected $model = MstBoxGachaGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_box_gacha_id' => fake()->uuid(),
            'box_level' => 1,
        ];
    }
}
