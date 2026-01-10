<?php

namespace Database\Factories;

use App\Domain\Stage\Models\UsrStageEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Stage\Eloquent\Models\UsrStageEvent>
 */
class UsrStageEventFactory extends Factory
{

    protected $model = UsrStageEvent::class;

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
            'mst_stage_id' => 1,
            'clear_count' => 0,
            'reset_clear_count' => 0,
            'reset_ad_challenge_count' => 0,
            'latest_reset_at' => fake()->dateTime(),
            'last_challenged_at' => fake()->dateTime(),
        ];
    }
}
