<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitGradeUp>
 */
class MstUnitGradeUpFactory extends Factory
{

    protected $model = MstUnitGradeUp::class;

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
            'require_amount' => '50',
        ];
    }
}
