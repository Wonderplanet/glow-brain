<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprGachaUpper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprGachaUpper>
 */
class OprGachaUpperFactory extends Factory
{

    protected $model = OprGachaUpper::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'upper_group' => 'None',
            'upper_type' => 'MaxRarity',
            'count' => 1,
        ];
    }
}
