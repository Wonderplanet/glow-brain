<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstPartyUnitCount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPartyUnitCount>
 */
class MstPartyUnitCountFactory extends Factory
{

    protected $model = MstPartyUnitCount::class;

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
            'mst_stage_id' => fake()->uuid(),
            'max_count' => fake()->numberBetween(1, 10),
        ];
    }
}
