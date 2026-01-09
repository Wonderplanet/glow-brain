<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstDummyUserUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstDummyUserUnit>
 */
class MstDummyUserUnitFactory extends Factory
{

    protected $model = MstDummyUserUnit::class;

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
            'mst_unit_id' => fake()->uuid(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
        ];
    }
}
