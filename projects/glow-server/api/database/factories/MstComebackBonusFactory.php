<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstComebackBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstComebackBonus>
 */
class MstComebackBonusFactory extends Factory
{

    protected $model = MstComebackBonus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_comeback_bonus_schedule_id' => '1',
            'login_day_count' => 1,
            'mst_daily_bonus_reward_group_id' => fake()->uuid(),
            'sort_order' => 0,
            'release_key' => 1,
        ];
    }
}
