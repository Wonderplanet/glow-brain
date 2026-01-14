<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstArtwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstArtwork>
 */
class MstArtworkFactory extends Factory
{

    protected $model = MstArtwork::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_series_id' => fake()->uuid(),
            'outpost_additional_hp' => fake()->numberBetween(1, 100),
            'asset_key' => fake()->uuid(),
            'sort_order' => fake()->numberBetween(1, 100),
            'release_key' => 1,
        ];
    }
}
