<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrUserLogin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Eloquent\Models\UsrUserLogin>
 */
class UsrUserLoginFactory extends Factory
{
    protected $model = UsrUserLogin::class;

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
            'first_login_at' => fake()->dateTimeThisYear(),
            'last_login_at' => fake()->dateTimeThisYear(),
            'hourly_accessed_at' => fake()->dateTimeThisYear(),
            'login_count' => fake()->numberBetween(1, 100),
            'login_day_count' => fake()->numberBetween(1, 100),
            'login_continue_day_count' => fake()->numberBetween(1, 100),
            'comeback_day_count' => fake()->numberBetween(1, 100),
        ];
    }
}
