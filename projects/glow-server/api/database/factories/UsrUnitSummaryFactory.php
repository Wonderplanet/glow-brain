<?php

namespace Database\Factories;

use App\Domain\Unit\Models\UsrUnitSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Unit\Models\UsrUnitSummary>
 */
class UsrUnitSummaryFactory extends Factory
{
    protected $model = UsrUnitSummary::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'grade_level_total_count' => 0,
        ];
    }
}
