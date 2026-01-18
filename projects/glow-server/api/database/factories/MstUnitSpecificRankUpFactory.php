<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp>
 */
class MstUnitSpecificRankUpFactory extends Factory
{

    protected $model = MstUnitSpecificRankUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'mst_unit_id' => fake()->uuid(),
            'rank' => 1,
            'amount' => 1,
            'unit_memory_amount' => 0,
            'require_level' => 1,
            'sr_memory_fragment_amount' => 0,
            'ssr_memory_fragment_amount' => 0,
            'ur_memory_fragment_amount' => 0,
        ];
    }
}
