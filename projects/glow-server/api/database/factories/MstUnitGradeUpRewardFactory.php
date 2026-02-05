<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitGradeUpReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitGradeUpReward>
 */
class MstUnitGradeUpRewardFactory extends Factory
{
    protected $model = MstUnitGradeUpReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numerify('mst_unit_grade_up_reward_#####'),
            'mst_unit_id' => 'unit_001',
            'grade_level' => 5,
            'resource_type' => 'Artwork',
            'resource_id' => 'artwork_001',
            'resource_amount' => 1,
        ];
    }
}
