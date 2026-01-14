<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionEventDailyI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionEventDailyI18n>
 */
class MstMissionEventDailyI18nFactory extends Factory
{

    protected $model = MstMissionEventDailyI18n::class;

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
            'mst_mission_event_daily_id' => 'mst_mission_event_daily_id',
            'language' => 'ja',
            'description' => 'description',
        ];
    }
}
