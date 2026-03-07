<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient>
 */
class MstUnitGradeCoefficientFactory extends Factory
{
    protected $model = MstUnitGradeCoefficient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'unit_label' => 'DropR',
            'grade_level' => 1,
            'coefficient' => 1,
            'release_key' => 1,
        ];
    }


}
