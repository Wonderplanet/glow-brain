<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstArtworkPanelMission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstArtworkPanelMission>
 */
class MstArtworkPanelMissionFactory extends Factory
{
    protected $model = MstArtworkPanelMission::class;

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
            'mst_artwork_id' => 'mst_artwork_id',
            'mst_event_id' => 'event',
            'initial_open_mst_artwork_fragment_id' => null,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
        ];
    }
}
