<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionEventDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionEventDependency>
 */
class MstMissionEventDependencyFactory extends Factory
{

    protected $model = MstMissionEventDependency::class;

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
            'mst_mission_event_id' => fake()->uuid(),
            'unlock_order' => 1,
        ];
    }
}
