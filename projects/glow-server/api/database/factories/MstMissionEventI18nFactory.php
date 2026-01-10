<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionEventI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionEventI18n>
 */
class MstMissionEventI18nFactory extends Factory
{

    protected $model = MstMissionEventI18n::class;

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
            'mst_mission_event_id' => 'mst_mission_event_id',
            'language' => 'ja',
            'description' => 'description',
        ];
    }
}
