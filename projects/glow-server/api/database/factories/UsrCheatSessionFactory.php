<?php

namespace Database\Factories;

use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Cheat\Models\UsrCheatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\AdventBattle\Eloquent\Models\UsrAdventBattle>
 */
class UsrCheatSessionFactory extends Factory
{
    protected $model = UsrCheatSession::class;

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
            'content_type' => CheatContentType::ADVENT_BATTLE->value,
            'target_id' => fake()->uuid(),
            'party_status' => json_encode([]),
        ];
    }
}
