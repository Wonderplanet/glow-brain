<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprAssetRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprAssetRelease>
 */
class OprAssetReleaseFactory extends Factory
{
    protected $model = OprAssetRelease::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 111,
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
