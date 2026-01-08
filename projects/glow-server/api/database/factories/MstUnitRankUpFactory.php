<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitRankUp>
 */
class MstUnitRankUpFactory extends Factory
{

    protected $model = MstUnitRankUp::class;

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
            'unit_label' => 'DropS',
            'require_level' => 1,
            'amount' => '1',
            'sr_memory_fragment_amount' => 0,
            'ssr_memory_fragment_amount' => 0,
            'ur_memory_fragment_amount' => 0,
        ];
    }
}
