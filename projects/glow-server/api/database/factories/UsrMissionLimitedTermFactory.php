<?php

namespace Database\Factories;

use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsrMissionLimitedTerm>
 */
class UsrMissionLimitedTermFactory extends Factory
{
    protected $model = UsrMissionLimitedTerm::class;

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
            'latest_reset_at' => fake()->dateTime(),
        ];
    }
}
