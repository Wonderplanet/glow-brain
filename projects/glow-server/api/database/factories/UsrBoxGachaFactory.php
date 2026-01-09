<?php

namespace Database\Factories;

use App\Domain\BoxGacha\Models\UsrBoxGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\BoxGacha\Models\UsrBoxGacha>
 */
class UsrBoxGachaFactory extends Factory
{
    protected $model = UsrBoxGacha::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_box_gacha_id' => fake()->uuid(),
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ];
    }
}
