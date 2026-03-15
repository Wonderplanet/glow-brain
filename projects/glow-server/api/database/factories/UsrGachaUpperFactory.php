<?php

namespace Database\Factories;

use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGachaUpper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Gacha\Eloquent\Models\UsrGachaUpper>
 */
class UsrGachaUpperFactory extends Factory
{
    protected $model = UsrGachaUpper::class;

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
            'upper_group' => fake()->uuid(),
            'upper_type' => UpperType::MAX_RARITY,
            'count' => 0,
        ];
    }
}
