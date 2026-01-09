<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstArtworkFragment>
 */
class MstArtworkFragmentFactory extends Factory
{

    protected $model = MstArtworkFragment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_artwork_id' => fake()->uuid(),
            'drop_group_id' => fake()->uuid(),
            'drop_percentage' => fake()->numberBetween(0, 100),
            'asset_num' => 0,
            'release_key' => 1,
        ];
    }
}
