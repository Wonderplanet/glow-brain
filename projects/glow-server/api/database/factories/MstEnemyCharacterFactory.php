<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstEnemyCharacter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstEnemyCharacter>
 */
class MstEnemyCharacterFactory extends Factory
{

    protected $model = MstEnemyCharacter::class;

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
            'mst_series_id' => fake()->uuid(),
            'asset_key' => 'asset_key',
            'is_displayed_encyclopedia' => 0,
        ];
    }
}
