<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect>
 */
class MstUnitEncyclopediaEffectFactory extends Factory
{

    protected $model = MstUnitEncyclopediaEffect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_unit_encyclopedia_reward_id' => 'unitEncyclopediaReward1',
            'effect_type' => '',
            'value' => 0,
            'release_key' => 1,
        ];
    }
}
