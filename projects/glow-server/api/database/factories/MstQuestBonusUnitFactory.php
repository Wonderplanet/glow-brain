<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstQuestBonusUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstQuestBonusUnit>
 */
class MstQuestBonusUnitFactory extends Factory
{

    protected $model = MstQuestBonusUnit::class;

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
            'mst_unit_id' => fake()->uuid(),
            'coin_bonus_rate' => 0.5,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
