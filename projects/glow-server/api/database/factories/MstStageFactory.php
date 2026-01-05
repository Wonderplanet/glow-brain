<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstStage>
 */
class MstStageFactory extends Factory
{
    protected $model = MstStage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_quest_id' => fake()->uuid(),
            'cost_stamina' => 1,
            'exp' => 2,
            'coin' => 3,
            'mst_artwork_fragment_drop_group_id' => fake()->uuid(),
            'prev_mst_stage_id' => fake()->uuid(),
            'mst_stage_tips_group_id' => fake()->uuid(),
            'auto_lap_type' => null,
            'max_auto_lap_count' => 1,
            'sort_order' => fake()->numberBetween(1, 100),
            'release_key' => fake()->numberBetween(1, 100),
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2031-01-01 00:00:00',
        ];
    }
}
