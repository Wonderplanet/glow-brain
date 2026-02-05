<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrUserParameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Eloquent\Models\UsrUserParameter>
 */
class UsrUserParameterFactory extends Factory
{
    protected $model = UsrUserParameter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'usr_user_id' => fake()->uuid(),
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 0,
            'stamina_updated_at' => now()->toDateTimeString(),
        ];
    }
}
