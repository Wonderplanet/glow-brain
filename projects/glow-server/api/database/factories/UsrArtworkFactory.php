<?php

namespace Database\Factories;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Encyclopedia\Eloquent\Models\UsrArtwork>
 */
class UsrArtworkFactory extends Factory
{
    protected $model = UsrArtwork::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_artwork_id' => fake()->uuid(),
        ];
    }
}
