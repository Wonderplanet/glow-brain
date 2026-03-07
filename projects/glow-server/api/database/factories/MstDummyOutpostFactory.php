<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstDummyOutpost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDummyOutpost>
 */
class MstDummyOutpostFactory extends Factory
{
    protected $model = MstDummyOutpost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' =>  1,
            'mst_dummy_user_id' => fake()->uuid(),
            'mst_outpost_enhancement_id' => fake()->uuid(),
            'level' => 1,
        ];
    }
}
