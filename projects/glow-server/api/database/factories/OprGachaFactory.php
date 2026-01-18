<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprGacha>
 */
class OprGachaFactory extends Factory
{

    protected $model = OprGacha::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'gacha_type' => 'Normal',
            'upper_group' => 'None',
            'enable_ad_play' => false,
            'enable_add_ad_play_upper' => false,
            'ad_play_interval_time' => null,
            'multi_draw_count' => 1,
            'multi_fixed_prize_count' => 0,
            'daily_play_limit_count' => 100,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 100,
            'total_ad_limit_count' => 100,
            'prize_group_id' => fake()->uuid(),
            'fixed_prize_group_id' => null,
            'appearance_condition' => 'Always',
            'unlock_condition_type' => 'None',
            'unlock_duration_hours' => null,
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
            'display_mst_unit_id' => null,
        ];
    }
}
