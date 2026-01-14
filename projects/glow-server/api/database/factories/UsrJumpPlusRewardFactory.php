<?php

namespace Database\Factories;

use App\Domain\JumpPlus\Models\UsrJumpPlusReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\JumpPlus\Eloquent\Models\UsrJumpPlusReward>
 */
class UsrJumpPlusRewardFactory extends Factory
{
    protected $model = UsrJumpPlusReward::class;

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
            'mng_jump_plus_reward_schedule_id' => fake()->uuid(),
        ];
    }
}
