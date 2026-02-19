<?php

namespace Database\Factories;

use App\Domain\InGame\Models\UsrEnemyDiscovery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\InGame\Eloquent\Models\UsrEnemyDiscovery>
 */
class UsrEnemyDiscoveryFactory extends Factory
{
    protected $model = UsrEnemyDiscovery::class;

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
            'mst_enemy_character_id' => fake()->uuid(),
        ];
    }
}
