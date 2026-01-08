<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstEventBonusUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstEventBonusUnitFactory extends Factory
{

    protected $model = MstEventBonusUnit::class;

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
            'mst_unit_id' => 'unit1',
            'bonus_percentage' => 10,
            'event_bonus_group_id' => 'eventBonusGroupId1',
            'is_pick_up' => 0
        ];
    }
}
