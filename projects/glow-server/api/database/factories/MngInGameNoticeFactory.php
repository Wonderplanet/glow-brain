<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngInGameNotice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngInGameNotice>
 */
class MngInGameNoticeFactory extends Factory
{

    protected $model = MngInGameNotice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'display_type' => fake()->randomElement(['BasicBanner', 'Dialog']),
            'destination_type' => 'InGame',
            'destination_path' => 'Gacha',
            'destination_path_detail' => '',
            'enable' => fake()->boolean(),
            'priority' => fake()->numberBetween(1, 100),
            'display_frequency_type' => fake()->randomElement(['Always', 'Daily', 'Weekly', 'Monthly', 'Once']),
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2035-12-31 23:59:59',
        ];
    }
}
