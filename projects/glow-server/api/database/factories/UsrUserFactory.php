<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Eloquent\Models\UsrUser>
 */
class UsrUserFactory extends Factory
{
    protected $model = UsrUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'game_start_at' => now()->toDateTimeString(),
        ];
    }
}
