<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstEvent>
 */
class MstEventFactory extends Factory
{

    protected $model = MstEvent::class;

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
            'mst_series_id' => 'mst_series_id',
            'is_displayed_series_logo' => 0,
            'is_displayed_jump_plus' => 0,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
            'asset_key' => 'asset_key',
        ];
    }
}
