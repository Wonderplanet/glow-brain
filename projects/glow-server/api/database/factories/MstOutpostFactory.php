<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstOutpost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstOutpost>
 */
class MstOutpostFactory extends Factory
{

    protected $model = MstOutpost::class;

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
            'asset_key' => 'asset_key',
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
