<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionLimitedTermDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Domain\Resource\Mst\Models\MstMissionLimitedTermDependency>
 */
class MstMissionLimitedTermDependencyFactory extends Factory
{

    protected $model = MstMissionLimitedTermDependency::class;

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
            'group_id' => fake()->uuid(),
            'mst_mission_limited_term_id' => fake()->uuid(),
            'unlock_order' => 1,
        ];
    }
}
