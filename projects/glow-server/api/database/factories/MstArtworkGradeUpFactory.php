<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstArtworkGradeUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstArtworkGradeUp>
 */
class MstArtworkGradeUpFactory extends Factory
{
    protected $model = MstArtworkGradeUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_artwork_id' => null,
            'mst_series_id' => fake()->uuid(),
            'rarity' => 'R',
            'grade_level' => 1,
        ];
    }
}
