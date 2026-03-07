<?php

declare(strict_types=1);

namespace Database\Factories\Log;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LogCurrencyFreeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usr_user_id' => '1',
            'os_platform' => 'iOS',
            'before_ingame_amount' => 0,
            'before_bonus_amount' => 0,
            'before_reward_amount' => 0,
            'change_ingame_amount' => 100,
            'change_bonus_amount' => 100,
            'change_reward_amount' => 100,
            'current_ingame_amount' => 100,
            'current_bonus_amount' => 100,
            'current_reward_amount' => 100,
            'trigger_type' => 'dummy',
            'trigger_id' => '',
            'trigger_name' => 'dummy log',
            'trigger_detail' => 'dummy log detail',
            'request_id' => fake()->uuid(),
        ];
    }
}
