<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUserLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUserLevel>
 */
class MstUserLevelFactory extends Factory
{

    protected $model = MstUserLevel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'level' => 1,
            'stamina' => 0,
            'exp' => 0,
        ];
    }
}
