<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprAssetReleaseControl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprAssetReleaseControl>
 */
class OprAssetReleaseControlFactory extends Factory
{
    protected $model = OprAssetReleaseControl::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'branch' => fake()->uuid(),
            'hash' => fake()->uuid(),
            'release_at' => fake()->dateTime(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
