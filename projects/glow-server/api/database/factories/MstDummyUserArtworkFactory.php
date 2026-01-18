<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstDummyUserArtwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDummyUserArtwork>
 */
class MstDummyUserArtworkFactory extends Factory
{

    protected $model = MstDummyUserArtwork::class;

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
            'mst_dummy_user_id' => fake()->uuid(),
            'mst_artwork_id' => fake()->uuid(),
        ];
    }
}
