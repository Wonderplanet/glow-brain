<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitRankCoefficient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitRankCoefficient>
 */
class MstUnitRankCoefficientFactory extends Factory
{
    protected $model = MstUnitRankCoefficient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'rank' => 1,
            'coefficient' => 1,
            'special_unit_coefficient' => 1,
            'release_key' => 1,
        ];
    }


}
